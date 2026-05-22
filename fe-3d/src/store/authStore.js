import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    userId: null,
    hoTen: localStorage.getItem('ho_ten') || '',
    email: localStorage.getItem('email') || '',
    vaiTro: localStorage.getItem('vai_tro') ? Number(localStorage.getItem('vai_tro')) : null,
    anhDaiDien: localStorage.getItem('anh_dai_dien') || '',
    tokenAdmin: localStorage.getItem('token_admin') || '',
    isValidated: false,
    loading: false,
  }),
  getters: {
    isAdminAuthenticated: (state) => !!state.tokenAdmin && state.isValidated && [1, 2, 4, 5].includes(state.vaiTro),
  },
  actions: {
    setAdminData(user, token) {
      this.userId = user.id;
      this.hoTen = user.ho_ten || '';
      this.email = user.email || '';
      this.vaiTro = Number(user.vai_tro);
      this.anhDaiDien = user.anh_dai_dien || '';
      this.tokenAdmin = token;
      this.isValidated = true;

      localStorage.setItem('token_admin', token);
      localStorage.setItem('ho_ten', this.hoTen);
      localStorage.setItem('email', this.email);
      localStorage.setItem('vai_tro', String(this.vaiTro));
      localStorage.setItem('anh_dai_dien', this.anhDaiDien);
      
      // Dispatch event to sync components
      window.dispatchEvent(new Event('profileUpdated'));
    },
    clearAdminData() {
      this.userId = null;
      this.hoTen = '';
      this.email = '';
      this.vaiTro = null;
      this.anhDaiDien = '';
      this.tokenAdmin = '';
      this.isValidated = false;

      localStorage.removeItem('token_admin');
      localStorage.removeItem('ho_ten');
      localStorage.removeItem('email');
      localStorage.removeItem('vai_tro');
      localStorage.removeItem('anh_dai_dien');
      localStorage.removeItem('ten_vai_tro');
      localStorage.removeItem('nguoi_dung_id');

      window.dispatchEvent(new Event('profileUpdated'));
    },
    async validateAdminToken() {
      const token = localStorage.getItem('token_admin');
      if (!token) {
        this.clearAdminData();
        return false;
      }

      this.loading = true;
      try {
        const response = await axios.get('/api/check-token', {
          headers: { Authorization: `Bearer ${token}` },
        });

        if (response.data && response.data.status) {
          const user = {
            id: response.data.id,
            ho_ten: response.data.ho_ten,
            email: response.data.email,
            vai_tro: response.data.vai_tro,
            anh_dai_dien: response.data.anh_dai_dien || '',
          };
          this.setAdminData(user, token);
          return true;
        } else {
          this.clearAdminData();
          return false;
        }
      } catch (error) {
        console.error('Error validating admin token:', error);
        this.clearAdminData();
        return false;
      } finally {
        this.loading = false;
      }
    }
  }
});
