<script setup lang="ts">
/**
 * Article List View - Brand Story Page
 * Redesigned to match mockup with hero, vision, mission, gallery sections
 */
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useArticles } from '../composables/useArticles'

const { t } = useI18n()

// Use composable for articles data
const {
    filteredArticles,
    isLoading
} = useArticles()

// Gallery images for product showcase
const galleryImages = [
    { id: 1, src: 'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?w=400', alt: 'Ceramic Tea Set' },
    { id: 2, src: 'https://images.unsplash.com/photo-1565193566173-7a0ee3dbe261?w=400', alt: 'Decorative Vase' },
    { id: 3, src: 'https://images.unsplash.com/photo-1610701596007-11502861dcfa?w=400', alt: 'Porcelain Bowl' },
    { id: 4, src: 'https://images.unsplash.com/photo-1493106641515-6b5631de4bb9?w=400', alt: 'Ceramic Plate' }
]

// Partner logos
const partners = [
    { id: 1, name: 'HEALTHYCOM', logo: '' },
    { id: 2, name: 'Gốm sứ', logo: '' },
    { id: 3, name: 'QTS', logo: '' }
]
</script>

<template>
    <div class="articles-brand-story">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-content">
                <h1 class="brand-name">Thiết kế gốm thủ công bản địa Việt Nam</h1>
                <p class="brand-tagline"> Gốm thủ công đương đại phát triển từ nền tảng nghề gốm truyền thống</p>
            </div>
            <div class="hero-images">
                <div class="hero-image hero-image-1">
                    <img src="https://images.unsplash.com/photo-1565193566173-7a0ee3dbe261?w=600" alt="Ceramic Art" />
                </div>
                <div class="hero-image hero-image-2">
                    <img src="https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?w=500" alt="Tea Set" />
                </div>
                <div class="hero-image hero-image-3">
                    <img src="https://images.unsplash.com/photo-1610701596007-11502861dcfa?w=400"
                        alt="Decorative Piece" />
                </div>
            </div>
        </section>

        <!-- Vision Section -->
        <section class="vision-section">
            <div class="container">
                <h2 class="section-title">Tầm nhìn</h2>
                <p class="vision-text">
                    * Chúng tôi thiết kế và làm gốm theo lối thủ công, kế thừa giá trị truyền thống và không ngừng trau
                    dồi, học tập thêm những kinh nghiệm, kiến thức từ trong nước và thế giới. Gốm Yên Lam được tạo hình
                    with ba phương pháp chính: nắn tay be chạch, khuôn, bàn xoay điện. Chúng tôi sử dụng men màu thô từ
                    các loại oxit kim loại và gốm được nung cao độ. Xưởng gốm của chúng tôi đặt tại Thuận An Bình Dương.
                    Xưởng có 1 lò gas thể tích 2 khối và quy tụ đội ngũ nghệ sỹ gốm lành nghề.
                </p>

                <div class="mission-grid">
                    <div class="mission-card">
                        <h3>Sứ mệnh</h3>
                        <p>Mang đến sự hoàn hảo trong từng sản phẩm, phục vụ khách hàng với chất lượng tốt nhất.</p>
                    </div>
                    <div class="mission-card">
                        <h3>Mô hình tập</h3>
                        <p>Kết hợp công nghệ tiên tiến với nghề thủ công truyền thống để tạo ra sản phẩm độc đáo.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Product Gallery Section -->
        <section class="gallery-section">
            <div class="container">
                <div class="gallery-grid">
                    <div v-for="image in galleryImages" :key="image.id" class="gallery-item">
                        <img :src="image.src" :alt="image.alt" />
                        <div class="gallery-overlay">
                            <span>{{ image.alt }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Customer Value Section -->
        <section class="value-section">
            <div class="container">
                <h2 class="section-title-light">GIÁ TRỊ KHÁCH HÀNG</h2>
                <p class="value-text">
                    Chúng tôi tin rằng mỗi sản phẩm gốm sứ không chỉ là vật dụng, mà còn là tác phẩm nghệ thuật
                    mang đậm giá trị văn hóa Việt Nam. Sự hài lòng của khách hàng là thước đo thành công của chúng tôi.
                </p>
            </div>
        </section>

        <!-- Articles List (if any) -->
        <section v-if="filteredArticles.length" class="articles-section">
            <div class="container">
                <h2 class="section-title">Bài viết mới nhất</h2>

                <div v-if="isLoading" class="loading-grid">
                    <div v-for="i in 3" :key="i" class="loading-card"></div>
                </div>

                <div v-else class="articles-grid">
                    <RouterLink v-for="article in filteredArticles.slice(0, 3)" :key="article.id"
                        :to="`/articles/${article.id}`" class="article-card">
                        <div class="article-image">
                            <img v-if="article.image || article.thumbnail" :src="article.image || article.thumbnail"
                                :alt="article.title" />
                            <div v-else class="article-placeholder">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="1">
                                    <path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4" />
                                </svg>
                            </div>
                        </div>
                        <div class="article-content">
                            <h3>{{ article.title }}</h3>
                            <p>{{ article.excerpt || article.content?.substring(0, 100) }}...</p>
                        </div>
                    </RouterLink>
                </div>
            </div>
        </section>
    </div>
</template>
