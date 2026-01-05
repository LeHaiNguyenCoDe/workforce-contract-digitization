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
                    với ba phương pháp chính: nắn tay be chạch, khuôn, bàn xoay điện. Chúng tôi sử dụng men màu thô từ
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

<style scoped>
.articles-brand-story {
    background: #fff;
}

/* Hero Section */
.hero-section {
    background: linear-gradient(135deg, #1B365D 0%, #2C4A7C 100%);
    min-height: 80vh;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
}

.hero-content {
    text-align: center;
    z-index: 10;
    margin-bottom: 3rem;
}

.brand-name {
    font-size: 3.5rem;
    font-weight: 700;
    color: #C9A962;
    margin-bottom: 1rem;
    font-family: 'Times New Roman', serif;
}

.brand-tagline {
    font-size: 1rem;
    color: #C9A962;
    letter-spacing: 0.2em;
    opacity: 0.9;
}

.hero-images {
    display: flex;
    gap: 2rem;
    justify-content: center;
    align-items: flex-end;
    flex-wrap: wrap;
    max-width: 1200px;
}

.hero-image {
    overflow: hidden;
    border-radius: 8px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    transition: transform 0.5s ease;
}

.hero-image:hover {
    transform: translateY(-10px);
}

.hero-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.hero-image-1 {
    width: 280px;
    height: 350px;
}

.hero-image-2 {
    width: 320px;
    height: 400px;
}

.hero-image-3 {
    width: 250px;
    height: 320px;
}

/* Vision Section */
.vision-section {
    background: #FEFBF2;
    padding: 5rem 2rem;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
}

.section-title {
    font-size: 2rem;
    font-weight: 600;
    color: #1B365D;
    text-align: center;
    margin-bottom: 2rem;
    position: relative;
}

.section-title::after {
    content: '';
    display: block;
    width: 60px;
    height: 3px;
    background: #C9A962;
    margin: 1rem auto 0;
}

.vision-text {
    text-align: center;
    color: #555;
    line-height: 1.8;
    max-width: 800px;
    margin: 0 auto 3rem;
    font-size: 1.1rem;
}

.mission-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.mission-card {
    background: #fff;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.mission-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.mission-card h3 {
    color: #1B365D;
    font-size: 1.3rem;
    margin-bottom: 1rem;
    font-weight: 600;
}

.mission-card p {
    color: #666;
    line-height: 1.6;
}

/* Gallery Section */
.gallery-section {
    padding: 4rem 2rem;
    background: #fff;
}

.gallery-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
}

.gallery-item {
    position: relative;
    aspect-ratio: 1;
    overflow: hidden;
    border-radius: 12px;
    cursor: pointer;
}

.gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.gallery-item:hover img {
    transform: scale(1.1);
}

.gallery-overlay {
    position: absolute;
    inset: 0;
    background: rgba(27, 54, 93, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.gallery-item:hover .gallery-overlay {
    opacity: 1;
}

.gallery-overlay span {
    color: #fff;
    font-weight: 500;
    text-align: center;
}

/* Customer Value Section */
.value-section {
    background: linear-gradient(135deg, #1B365D 0%, #2C4A7C 100%);
    padding: 5rem 2rem;
    color: #fff;
}

.section-title-light {
    font-size: 1.8rem;
    font-weight: 600;
    color: #C9A962;
    text-align: center;
    margin-bottom: 2rem;
    letter-spacing: 0.1em;
}

.value-text {
    text-align: center;
    color: rgba(255, 255, 255, 0.85);
    line-height: 1.8;
    max-width: 700px;
    margin: 0 auto 3rem;
}

.awards-section {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 3rem;
    margin-top: 3rem;
    padding: 2rem;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    flex-wrap: wrap;
}

.awards-title {
    color: #C9A962;
    font-size: 1.2rem;
    font-weight: 600;
    line-height: 1.4;
}

.awards-description p {
    color: rgba(255, 255, 255, 0.8);
    max-width: 400px;
}

/* Partners Section */
.partners-section {
    padding: 4rem 2rem;
    background: #FEFBF2;
    text-align: center;
}

.partners-label {
    color: #888;
    font-size: 0.9rem;
    letter-spacing: 0.1em;
    margin-bottom: 2rem;
}

.partners-grid {
    display: flex;
    justify-content: center;
    gap: 4rem;
    flex-wrap: wrap;
}

.partner-logo {
    display: flex;
    align-items: center;
    justify-content: center;
}

.partner-name {
    font-size: 1.3rem;
    font-weight: 700;
    color: #1B365D;
    letter-spacing: 0.05em;
}

/* Articles Section */
.articles-section {
    padding: 5rem 2rem;
    background: #fff;
}

.loading-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
}

.loading-card {
    height: 300px;
    background: #f0f0f0;
    border-radius: 12px;
    animation: pulse 1.5s infinite;
}

@keyframes pulse {

    0%,
    100% {
        opacity: 1;
    }

    50% {
        opacity: 0.5;
    }
}

.articles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 2rem;
}

.article-card {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    text-decoration: none;
}

.article-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
}

.article-image {
    aspect-ratio: 16/9;
    overflow: hidden;
}

.article-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.article-card:hover .article-image img {
    transform: scale(1.05);
}

.article-placeholder {
    width: 100%;
    height: 100%;
    background: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ccc;
}

.article-content {
    padding: 1.5rem;
}

.article-content h3 {
    color: #1B365D;
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.article-content p {
    color: #666;
    font-size: 0.9rem;
    line-height: 1.6;
}

/* Decorative Footer */
.decorative-footer {
    background: linear-gradient(135deg, #1B365D 0%, #2C4A7C 100%);
    padding: 3rem 2rem;
}

.footer-images {
    display: flex;
    justify-content: center;
    gap: 2rem;
}

.footer-images img {
    width: 150px;
    height: 150px;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid rgba(201, 169, 98, 0.5);
    opacity: 0.8;
    transition: opacity 0.3s ease;
}

.footer-images img:hover {
    opacity: 1;
}

/* Responsive */
@media (max-width: 1024px) {
    .gallery-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .brand-name {
        font-size: 2.5rem;
    }

    .hero-images {
        flex-direction: column;
        align-items: center;
    }

    .hero-image-1,
    .hero-image-2,
    .hero-image-3 {
        width: 80%;
        max-width: 300px;
        height: auto;
        aspect-ratio: 4/5;
    }

    .gallery-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .awards-section {
        flex-direction: column;
        text-align: center;
    }

    .loading-grid,
    .articles-grid {
        grid-template-columns: 1fr;
    }
}
</style>
