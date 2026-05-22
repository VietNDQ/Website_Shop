export function slugify(text) {
  if (!text) return "";
  return text
    .toString()
    .toLowerCase()
    .replace(/á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/g, "a")
    .replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/g, "e")
    .replace(/i|í|ì|ỉ|ĩ|ị/g, "i")
    .replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/g, "o")
    .replace(/ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự/g, "u")
    .replace(/ý|ỳ|ỷ|ỹ|ỵ/g, "y")
    .replace(/đ/g, "d")
    .replace(/\s+/g, "-") // thay thế khoảng trắng bằng dấu -
    .replace(/[^\w-]+/g, "") // xóa ký tự đặc biệt
    .replace(/--+/g, "-") // thu gọn nhiều dấu - liên tiếp
    .replace(/^-+/, "") // xóa dấu - ở đầu
    .replace(/-+$/, ""); // xóa dấu - ở cuối
}

export function getProductDetailUrl(product, variantId = null) {
  if (!product) return "/";
  const id = product.id || product.id_san_pham;
  const name = product.ten_san_pham || product.name || "san-pham";
  const slug = slugify(name);
  const path = `/${slug}-i.${id}`;
  if (variantId) {
    return `${path}?variant_id=${variantId}`;
  }
  return path;
}
