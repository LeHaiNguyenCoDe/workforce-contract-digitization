/**
 * Landing Products Column Configs
 */

export const productColumns = [
    { key: 'name', label: 'Sản phẩm' },
    { key: 'category', label: 'Danh mục' },
    { key: 'price', label: 'Giá' },
    { key: 'rating', label: 'Đánh giá' }
]

export const sortOptions = [
    { value: 'newest', label: 'Mới nhất' },
    { value: 'price_asc', label: 'Giá thấp đến cao' },
    { value: 'price_desc', label: 'Giá cao đến thấp' },
    { value: 'popular', label: 'Phổ biến nhất' }
]

export const priceRanges = [
    { min: 0, max: 100000, label: 'Dưới 100k' },
    { min: 100000, max: 500000, label: '100k - 500k' },
    { min: 500000, max: 1000000, label: '500k - 1 triệu' },
    { min: 1000000, max: 5000000, label: '1 - 5 triệu' },
    { min: 5000000, max: 0, label: 'Trên 5 triệu' }
]
