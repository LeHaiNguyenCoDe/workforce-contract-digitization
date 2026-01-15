<script lang="ts">
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'FoodieFooter',
  setup() {
    const currentYear = new Date().getFullYear()
    return { currentYear }
  }
})
</script>

<template>
  <footer class="foodie-footer">
    <div class="foodie-container">
      <div class="foodie-footer__grid">
        <!-- Logo & About -->
        <div class="foodie-footer__section">
          <a href="#" class="foodie-footer__logo">
            <span class="foodie-footer__logo-text">Pott<span class="highlight">ery</span></span>
          </a>
          <p class="foodie-footer__desc">
            Handcrafted ceramics made with love. Elevate your home with our unique, sustainable, and timeless pottery collections.
          </p>
        </div>

        <!-- Quick Links -->
        <div class="foodie-footer__section">
          <h4 class="foodie-footer__title">Quick Links</h4>
          <nav class="foodie-footer__nav">
            <a href="#">About Us</a>
            <a href="#">How It Works</a>
            <a href="#">Partner With Us</a>
            <a href="#">Careers</a>
          </nav>
        </div>

        <!-- Support -->
        <div class="foodie-footer__section">
          <h4 class="foodie-footer__title">Support</h4>
          <nav class="foodie-footer__nav">
            <a href="#">FAQs</a>
            <a href="#">Contact Us</a>
            <a href="#">Terms of Service</a>
            <a href="#">Privacy Policy</a>
          </nav>
        </div>

        <!-- Download App -->
        <div class="foodie-footer__section">
          <h4 class="foodie-footer__title">Get the App</h4>
          <div class="foodie-footer__apps">
            <a href="#" class="foodie-footer__store-btn">
              <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.1 2.48-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5 1.13 3.28 1.13.78 0 2.21-1.15 3.74-1 1.15.02 2.15.42 2.89 1.48-2.55 1.5-2.13 4.88.58 6.03-.69 1.73-1.61 3.49-2.6 4.98zM13 5c.44-2.34-1.38-4.44-3.5-4.44-.06 2.3 2.14 4.4 3.5 4.44z"/>
              </svg>
              App Store
            </a>
            <a href="#" class="foodie-footer__store-btn">
              <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                <path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.6 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.16,10.81C20.5,11.08 20.75,11.5 20.75,12C20.75,12.5 20.53,12.9 20.18,13.18L17.89,14.5L15.39,12L17.89,9.5L20.16,10.81M6.05,2.66L16.81,8.88L14.54,11.15L6.05,2.66Z"/>
              </svg>
              Playstore
            </a>
          </div>
        </div>
      </div>

      <!-- Copyright -->
      <div class="foodie-footer__bottom">
        <p>&copy; {{ currentYear }} Pottery. All rights reserved.</p>
        <div class="foodie-footer__social">
          <a href="#" aria-label="Facebook">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
              <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
            </svg>
          </a>
          <a href="#" aria-label="Instagram">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
              <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
              <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
            </svg>
          </a>
          <a href="#" aria-label="Twitter">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
              <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"/>
            </svg>
          </a>
        </div>
      </div>
    </div>
  </footer>
</template>

<style scoped>
.foodie-footer {
  background-color: #2c2a29 !important; /* Warm dark charcoal/brown for pottery theme */
  background-image: none !important;
  color: #e5e5e5;
  padding: 80px 0 40px;
  font-family: 'DM Sans', sans-serif;
  position: relative;
  z-index: 10;
  overflow: hidden;
  border: none !important;
  box-shadow: none !important;
  margin-top: 0 !important;
}

.foodie-footer::before,
.foodie-footer::after {
  content: none !important;
  display: none !important;
}

.foodie-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
}

.foodie-footer__grid {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1.5fr;
  gap: 60px;
  margin-bottom: 60px;
}

.foodie-footer__logo {
  display: inline-block;
  text-decoration: none;
  font-size: 28px;
  font-weight: 700;
  color: #fff;
  margin-bottom: 24px;
  letter-spacing: -0.5px;
}

.foodie-footer__logo-text .highlight {
  color: #d4a373; /* Terracotta/Clay color */
}

.foodie-footer__desc {
  font-size: 15px;
  line-height: 1.6;
  color: #9ca3af;
  max-width: 300px;
}

.foodie-footer__title {
  font-size: 18px;
  font-weight: 600;
  color: #fff;
  margin-bottom: 24px;
}

.foodie-footer__nav {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.foodie-footer__nav a {
  text-decoration: none;
  color: #9ca3af;
  font-size: 15px;
  transition: all 0.2s ease;
}

.foodie-footer__nav a:hover {
  color: #d4a373;
  transform: translateX(4px);
}

.foodie-footer__apps {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.foodie-footer__store-btn {
  display: flex;
  align-items: center;
  gap: 12px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 12px 20px;
  border-radius: 8px;
  color: #fff;
  text-decoration: none;
  font-size: 15px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.foodie-footer__store-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: #d4a373;
  transform: translateY(-2px);
}

.foodie-footer__bottom {
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  padding-top: 32px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 14px;
  color: #6b7280;
}

.foodie-footer__social {
  display: flex;
  gap: 20px;
}

.foodie-footer__social a {
  color: #9ca3af;
  transition: color 0.2s ease;
  display: flex;
  align-items: center;
}

.foodie-footer__social a:hover {
  color: #d4a373;
}

@media (max-width: 1024px) {
  .foodie-footer__grid {
    grid-template-columns: 1fr 1fr;
    gap: 40px;
  }
}

@media (max-width: 600px) {
  .foodie-footer__grid {
    grid-template-columns: 1fr;
    gap: 40px;
  }

  .foodie-footer__bottom {
    flex-direction: column;
    gap: 20px;
    text-align: center;
  }
}
</style>
