import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { PostModel } from '../models/home'

export const usePostStore = defineStore('landing-post', () => {
    const posts = ref<PostModel[]>([])
    const featuredPosts = ref<PostModel[]>([])
    const isLoading = ref(false)
    const currentPost = ref<PostModel | null>(null)

    const hasPosts = computed(() => posts.value.length > 0)
    const hasFeaturedPosts = computed(() => featuredPosts.value.length > 0)

    function generateMockPosts(): PostModel[] {
        return [
            {
                id: 1,
                slug: '10-cach-thiet-ke-phong-khach-tot-nhat',
                title: '10 cách thiết kế phòng khách tốt nhất',
                image: 'https://noithatm8.com/wp-content/uploads/sofa-goc-L-hien-dai-2-1.jpg',
                link: '#'
            },
            {
                id: 2,
                slug: 'trang-tri-cay-canh-hop-ly',
                title: 'Trang trí cây cảnh hợp lý',
                image: 'https://canhosaigonlandapartment.com/wp-content/uploads/2024/07/277675925_114118001240495_4179969127421716240_n.jpg',
                link: '#'
            },
            {
                id: 3,
                slug: 'setup-do-dung-ngoai-troi-nhu-the-nao',
                title: 'Setup đồ dùng ngoài trời như thế nào?',
                image: 'https://noithattoz.com/wp-content/uploads/2022/12/sofa-da-sf2-20.jpg',
                link: '#'
            },
            {
                id: 4,
                slug: 'xu-huong-thiet-ke-noi-that-2026',
                title: 'Xu hướng thiết kế nội thất 2026',
                image: 'https://noithattoz.com/wp-content/uploads/2020/12/sofa-ni-1-3.jpg',
                link: '#'
            }
        ]
    }

    async function fetchPosts(limit?: number) {
        isLoading.value = true
        try {
            await new Promise(resolve => setTimeout(resolve, 500))
            const mockData = generateMockPosts()
            posts.value = limit ? mockData.slice(0, limit) : mockData
        } catch (error) {
            console.error('Failed to fetch posts:', error)
            posts.value = []
        } finally {
            isLoading.value = false
        }
    }

    async function fetchFeaturedPosts(limit = 4) {
        isLoading.value = true
        try {
            await new Promise(resolve => setTimeout(resolve, 500))
            const mockData = generateMockPosts()
            featuredPosts.value = mockData.slice(0, limit)
        } catch (error) {
            console.error('Failed to fetch featured posts:', error)
            featuredPosts.value = []
        } finally {
            isLoading.value = false
        }
    }

    async function fetchPostBySlug(slug: string) {
        isLoading.value = true
        try {
            await new Promise(resolve => setTimeout(resolve, 500))
            const mockData = generateMockPosts()
            currentPost.value = mockData.find(post => post.slug === slug) || null
        } catch (error) {
            console.error('Failed to fetch post:', error)
            currentPost.value = null
        } finally {
            isLoading.value = false
        }
    }

    function reset() {
        posts.value = []
        featuredPosts.value = []
        currentPost.value = null
    }

    return {
        posts,
        featuredPosts,
        currentPost,
        isLoading,
        hasPosts,
        hasFeaturedPosts,
        fetchPosts,
        fetchFeaturedPosts,
        fetchPostBySlug,
        reset
    }
})
