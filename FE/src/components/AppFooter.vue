<script setup lang="ts">
import { RouterLink } from 'vue-router'

const currentYear = new Date().getFullYear()
</script>

<template>
  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <!-- Brand -->
        <div class="footer-section">
          <h3 class="footer-logo gradient-text">Store</h3>
          <p class="footer-desc">
            Nền tảng mua sắm trực tuyến hàng đầu với hàng ngàn sản phẩm chất lượng cao.
          </p>
        </div>

        <!-- Quick Links -->
        <div class="footer-section">
          <h4 class="footer-title">Liên kết nhanh</h4>
          <ul class="footer-links">
            <li><RouterLink to="/">Trang chủ</RouterLink></li>
            <li><RouterLink to="/products">Sản phẩm</RouterLink></li>
            <li><RouterLink to="/articles">Bài viết</RouterLink></li>
            <li><RouterLink to="/promotions">Khuyến mãi</RouterLink></li>
          </ul>
        </div>

        <!-- Support -->
        <div class="footer-section">
          <h4 class="footer-title">Hỗ trợ</h4>
          <ul class="footer-links">
            <li><RouterLink to="/faq">Câu hỏi thường gặp</RouterLink></li>
            <li><RouterLink to="/shipping">Chính sách vận chuyển</RouterLink></li>
            <li><RouterLink to="/returns">Đổi trả hàng</RouterLink></li>
            <li><RouterLink to="/contact">Liên hệ</RouterLink></li>
          </ul>
        </div>

        <!-- Contact -->
        <div class="footer-section">
          <h4 class="footer-title">Liên hệ</h4>
          <ul class="footer-contact">
            <li>
              <BaseIcon name="phone" :size="18" />
              <span>1800 1234</span>
            </li>
            <li>
              <BaseIcon name="mail" :size="18" />
              <span>support@store.io</span>
            </li>
          </ul>
        </div>
      </div>

      <div class="footer-bottom">
        <p>&copy; {{ currentYear }} Store. Tất cả quyền được bảo lưu.</p>
      </div>
    </div>
  </footer>
</template>

<style scoped>
.footer {
  background: var(--color-bg-secondary);
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding: var(--space-12) 0 var(--space-6);
  margin-top: auto;
}

.footer-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: var(--space-8);
  margin-bottom: var(--space-8);
}

.footer-logo {
  font-family: var(--font-display);
  font-size: var(--text-2xl);
  font-weight: 700;
  margin-bottom: var(--space-4);
}

.footer-desc {
  font-size: var(--text-sm);
  color: var(--color-text-muted);
  line-height: 1.6;
}

.footer-title {
  font-size: var(--text-base);
  font-weight: 600;
  margin-bottom: var(--space-4);
  color: var(--color-text-primary);
}

.footer-links {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.footer-links a {
  font-size: var(--text-sm);
  color: var(--color-text-muted);
  transition: color var(--transition-fast);
}

.footer-links a:hover {
  color: var(--color-primary-light);
}

.footer-contact {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

.footer-contact li {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  font-size: var(--text-sm);
  color: var(--color-text-muted);
}

.footer-contact svg {
  color: var(--color-primary);
}

.footer-bottom {
  padding-top: var(--space-6);
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  text-align: center;
}

.footer-bottom p {
  font-size: var(--text-sm);
  color: var(--color-text-muted);
}

@media (max-width: 768px) {
  .footer-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-6);
  }
}

@media (max-width: 480px) {
  .footer-grid {
    grid-template-columns: 1fr;
  }
}
</style>
