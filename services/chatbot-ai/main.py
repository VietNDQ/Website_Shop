import os
import logging
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from typing import List, Optional
import requests
from dotenv import load_dotenv

# Tải cấu hình biến môi trường từ .env
load_dotenv()

# Tạo thư mục logs nếu chưa tồn tại
os.makedirs("logs", exist_ok=True)

# Cấu hình logging ghi nhật ký ra file và console
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(message)s",
    handlers=[
        logging.FileHandler(os.path.join("logs", "chatbot.log"), encoding="utf-8"),
        logging.StreamHandler()
    ]
)

app = FastAPI(
    title="BALAB AI Advanced Chatbot Service",
    description="Python microservice supporting Function Calling and Logging for Shop BALAB",
    version="2.0.0"
)

class MessageItem(BaseModel):
    role: str
    text: str

class ChatRequest(BaseModel):
    message: str
    history: List[MessageItem] = []
    categories: List[str] = []
    products: List[str] = []

# --- LOCAL TOOLS DEFINITIONS (API Calls to Laravel) ---

def local_search_products(query: str) -> dict:
    laravel_url = os.getenv("LARAVEL_API_URL", "http://127.0.0.1:8000")
    url = f"{laravel_url}/api/tim-kiem"
    try:
        logging.info(f"[Tool Call] Đang tìm kiếm sản phẩm với từ khóa: '{query}'")
        response = requests.get(url, params={"q": query, "limit": 5})
        if response.status_code == 200:
            data = response.json()
            products = data.get("products", [])
            result_list = []
            for p in products:
                status_str = "Còn hàng" if p.get("tinh_trang") == 1 else "Hết hàng"
                price_val = p.get("gia_co_ban", 0)
                try:
                    price_str = f"{int(float(price_val)):,} đ"
                except Exception:
                    price_str = f"{price_val} đ"
                
                result_list.append({
                    "id": p.get("id"),
                    "ten_san_pham": p.get("ten_san_pham"),
                    "gia": price_str,
                    "tinh_trang": status_str,
                    "danh_muc": p.get("danh_muc", {}).get("ten_danh_muc", "Mô hình")
                })
            logging.info(f"[Tool Success] Đã tìm thấy {len(result_list)} sản phẩm phù hợp.")
            return {"products": result_list}
        else:
            logging.warning(f"[Tool Warning] Laravel API trả về mã lỗi {response.status_code}")
            return {"error": "Không thể kết nối đến cơ sở dữ liệu sản phẩm lúc này."}
    except Exception as e:
        logging.error(f"[Tool Error] Lỗi trong local_search_products: {str(e)}")
        return {"error": f"Lỗi kết nối DB: {str(e)}"}

def local_check_order_status(ma_don_hang: str) -> dict:
    laravel_url = os.getenv("LARAVEL_API_URL", "http://127.0.0.1:8000")
    url = f"{laravel_url}/api/chatbot/order-status/{ma_don_hang}"
    try:
        logging.info(f"[Tool Call] Đang tra cứu đơn hàng mã: '{ma_don_hang}'")
        response = requests.get(url)
        if response.status_code == 200:
            logging.info(f"[Tool Success] Đã tìm thấy thông tin đơn hàng {ma_don_hang}.")
            return response.json()
        elif response.status_code == 404:
            logging.warning(f"[Tool Warning] Không tìm thấy đơn hàng {ma_don_hang}.")
            return {"error": f"Không tìm thấy thông tin đơn hàng {ma_don_hang}."}
        else:
            logging.warning(f"[Tool Warning] Laravel API trả về mã lỗi {response.status_code}")
            return {"error": "Lỗi máy chủ khi truy vấn trạng thái đơn hàng."}
    except Exception as e:
        logging.error(f"[Tool Error] Lỗi trong local_check_order_status: {str(e)}")
        return {"error": f"Lỗi kết nối đơn hàng: {str(e)}"}

# --- END TOOLS ---

@app.post("/chat")
def chat_endpoint(request: ChatRequest):
    api_key = os.getenv("GEMINI_API_KEY")
    if not api_key:
        logging.error("GEMINI_API_KEY chưa được cấu hình ở môi trường Python.")
        raise HTTPException(
            status_code=500,
            detail="GEMINI_API_KEY chưa được cấu hình trong Python Service"
        )
    
    logging.info(f"[New Chat] Nhận tin nhắn: '{request.message}' | Lịch sử: {len(request.history)} tin nhắn")
    
    # 1. Định nghĩa danh sách các hàm (Tools) cung cấp cho Gemini
    tools = [
        {
            "functionDeclarations": [
                {
                    "name": "search_products",
                    "description": "Tìm kiếm các sản phẩm trong cửa hàng dựa trên từ khóa tìm kiếm. Sử dụng khi khách hàng muốn tìm sản phẩm, hỏi xem shop có bán mô hình gì, hoặc hỏi về giá cả sản phẩm cụ thể.",
                    "parameters": {
                        "type": "OBJECT",
                        "properties": {
                            "query": {"type": "STRING", "description": "Từ khóa tên sản phẩm cần tìm"}
                        },
                        "required": ["query"]
                    }
                },
                {
                    "name": "check_order_status",
                    "description": "Tra cứu thông tin trạng thái đơn hàng dựa trên mã đơn hàng. Sử dụng khi khách hàng hỏi về trạng thái đơn, vị trí đơn hàng hoặc hỏi mã đơn hàng cụ thể bắt đầu bằng ORD-.",
                    "parameters": {
                        "type": "OBJECT",
                        "properties": {
                            "ma_don_hang": {"type": "STRING", "description": "Mã đơn hàng của khách (ví dụ ORD-ABC123XYZ)"}
                        },
                        "required": ["ma_don_hang"]
                    }
                }
            ]
        }
    ]

    # 2. Ngữ cảnh tĩnh ban đầu (Static context) làm fallback
    products_context = ""
    for p_text in request.products:
        products_context += f"- {p_text}\n"
    categories_context = ", ".join(request.categories)

    # 3. System Instruction
    system_instruction = (
        "Bạn là trợ lý ảo (Chatbot tư vấn) thông minh của Shop BALAB - thương hiệu chuyên mô hình in 3D cao cấp (FDM và Resin).\n\n"
        "QUY TẮC PHẢN HỒI:\n"
        "1. Trả lời lịch sự, thân thiện, xưng hô là 'Shop' hoặc 'BALAB' và gọi khách hàng là 'bạn' hoặc 'quý khách'.\n"
        "2. Trả lời bằng tiếng Việt, ngắn gọn, súc tích và tập trung vào nhu cầu của khách hàng.\n"
        "3. Sử dụng các công cụ gọi hàm (Tools) được cung cấp để tra cứu dữ liệu thời gian thực. Hãy ưu tiên dùng các công cụ này trước để trả lời câu hỏi của khách hàng thay vì thông tin tĩnh.\n"
        "4. Nếu khách hàng muốn đặt in 3D theo yêu cầu, hãy khuyên họ gửi file thiết kế cho shop qua hotline/Zalo 1800 2097 hoặc bấm nút 'Nhận in 3D theo yêu cầu' trên menu.\n"
        "5. KHÔNG tự bịa ra thông tin sản phẩm hoặc trạng thái đơn hàng nếu công cụ gọi hàm trả về lỗi hoặc không tìm thấy.\n\n"
        "THÔNG TIN CHÍNH SÁCH CỬA HÀNG:\n"
        "- Giao hàng: Miễn phí cho đơn hàng từ 500.000đ. Hỗ trợ ship hỏa tốc nội thành.\n"
        "- Đóng gói: Bọc chống sốc cực kỳ kỹ lượng bảo vệ mô hình không gãy vỡ.\n"
        "- Bảo hành & Đổi trả: Đổi mới hoặc in lại trong 7 ngày nếu sản phẩm bị cong vênh hoặc lỗi từ xưởng sản xuất.\n\n"
        f"DANH MỤC SẢN PHẨM CÓ SẴN:\n{categories_context}\n\n"
        f"DANH SÁCH MỘT SỐ SẢN PHẨM NỔI BẬT (FALLBACK CONTEXT):\n{products_context}"
    )

    # 4. Chuẩn bị hội thoại gửi đi
    contents = []
    for msg in request.history:
        role = "user" if msg.role == "user" else "model"
        contents.append({
            "role": role,
            "parts": [{"text": msg.text}]
        })
        
    contents.append({
        "role": "user",
        "parts": [{"text": request.message}]
    })

    # 5. Gọi Gemini lần 1 (Kiểm tra xem AI có muốn gọi hàm không)
    url = f"https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={api_key}"
    payload = {
        "contents": contents,
        "systemInstruction": {
            "parts": [{"text": system_instruction}]
        },
        "tools": tools,
        "generationConfig": {
            "temperature": 0.7,
            "maxOutputTokens": 800
        }
    }

    try:
        response = requests.post(url, json=payload, headers={"Content-Type": "application/json"})
        if response.status_code != 200:
            logging.error(f"Gemini API Lần 1 thất bại: {response.text}")
            raise HTTPException(status_code=502, detail="Lỗi kết nối Gemini API.")

        result = response.json()
        candidate = result.get('candidates', [{}])[0]
        parts = candidate.get('content', {}).get('parts', [])
        
        # Kiểm tra xem có chứa yêu cầu gọi hàm không
        function_call = None
        for part in parts:
            if 'functionCall' in part:
                function_call = part['functionCall']
                break

        if function_call:
            func_name = function_call.get('name')
            func_args = function_call.get('args', {})
            func_id = function_call.get('id')  # Có thể có id hoặc không tùy version
            
            logging.info(f"[AI Request Function] Yêu cầu gọi hàm: '{func_name}' với đối số: {func_args}")

            # Thực thi hàm tương ứng ở local
            tool_result = {}
            if func_name == "search_products":
                tool_result = local_search_products(func_args.get("query", ""))
            elif func_name == "check_order_status":
                tool_result = local_check_order_status(func_args.get("ma_don_hang", ""))
            else:
                logging.warning(f"Không nhận diện được hàm: '{func_name}'")
                tool_result = {"error": "Hàm không khả dụng"}

            # Đưa yêu cầu gọi hàm của model vào lịch sử contents
            model_part = {
                "functionCall": {
                    "name": func_name,
                    "args": func_args
                }
            }
            if func_id:
                model_part["functionCall"]["id"] = func_id
                
            contents.append({
                "role": "model",
                "parts": [model_part]
            })

            # Đưa phản hồi từ hàm thực tế của shop vào lịch sử contents
            user_part = {
                "functionResponse": {
                    "name": func_name,
                    "response": tool_result
                }
            }
            if func_id:
                user_part["functionResponse"]["id"] = func_id

            contents.append({
                "role": "user",
                "parts": [user_part]
            })

            # Gọi Gemini lần 2 (Đưa kết quả thực tế cho AI tổng hợp câu trả lời)
            payload_step2 = {
                "contents": contents,
                "systemInstruction": {
                    "parts": [{"text": system_instruction}]
                },
                "tools": tools, # Giữ nguyên định nghĩa tool
                "generationConfig": {
                    "temperature": 0.7,
                    "maxOutputTokens": 800
                }
            }
            
            logging.info("[AI Request Final] Gửi kết quả gọi hàm lên Gemini lần 2...")
            response_step2 = requests.post(url, json=payload_step2, headers={"Content-Type": "application/json"})
            if response_step2.status_code != 200:
                logging.error(f"Gemini API Lần 2 thất bại: {response_step2.text}")
                raise HTTPException(status_code=502, detail="Lỗi kết nối Gemini API lần 2.")
                
            result_step2 = response_step2.json()
            reply_text = result_step2['candidates'][0]['content']['parts'][0]['text']
            
            logging.info(f"[Response Success] AI trả lời sau khi gọi hàm: '{reply_text}'")
            return {"status": True, "reply": reply_text}

        else:
            # Không yêu cầu gọi hàm, trả về trực tiếp
            reply_text = parts[0].get('text', '')
            logging.info(f"[Response Success] AI trả lời trực tiếp: '{reply_text}'")
            return {"status": True, "reply": reply_text}

    except Exception as e:
        logging.error(f"[Fatal Error] Lỗi nghiêm trọng tại chat_endpoint: {str(e)}")
        raise HTTPException(
            status_code=500,
            detail=f"Đã xảy ra lỗi nghiêm trọng: {str(e)}"
        )

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="127.0.0.1", port=8001)
