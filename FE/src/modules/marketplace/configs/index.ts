import type { Product, Category, Article, Store } from '../types';

/**
 * ==========================================
 * NAVIGATION & MENU CONFIGURATIONS
 * ==========================================
 */

export const MENU_ITEMS = [
  'Promotions',
  'Stores',
  'Our Contacts',
  'Delivery & Return',
  'Outlet'
];

export const SIDEBAR_CATEGORIES = [
  { 
    name: 'Laptops, Tablets & PCs', 
    icon: 'fas fa-laptop', 
    hasMega: true,
    megaData: [
      { title: 'Laptops', items: ['Apple MacBook', 'Business Laptop', 'Gaming Laptop', 'Ultrabook'] },
      { title: 'Tablets', items: ['Apple iPad', 'Android tablets', 'Windows Tablets'] },
      { title: 'PCs', items: ['Gaming PCs', 'Office PCs', 'All in one'] }
    ]
  },
  { 
    name: 'Computer & Office', 
    icon: 'fas fa-desktop', 
    hasMega: true, 
    megaData: [
      { title: 'Monitors', items: ['4K Monitors', 'Gaming Monitors', 'Office Monitors'] },
      { title: 'Printers', items: ['Laser Printers', 'Inkjet Printers', 'Scanners'] },
      { title: 'Storage', items: ['External HDD', 'SSD', 'USB Flash'] }
    ] 
  },
  { 
    name: 'Hardware & Components', 
    icon: 'fas fa-server', 
    hasMega: true, 
    megaData: [
      { title: 'Processors', items: ['Intel Core', 'AMD Ryzen', 'Server CPUs'] },
      { title: 'Graphics', items: ['NVIDIA RTX', 'AMD Radeon', 'Workstation GPUs'] },
      { title: 'RAM', items: ['DDR4', 'DDR5', 'Laptop Memory'] }
    ] 
  },
  { name: 'Smartphones', icon: 'fas fa-mobile-alt', hasMega: false },
  { name: 'Games & Entertainment', icon: 'fas fa-gamepad', hasMega: false },
  { name: 'Photo & Video', icon: 'fas fa-camera', hasMega: false },
  { name: 'Home Appliance', icon: 'fas fa-blender', hasMega: false }
];

/**
 * ==========================================
 * PRODUCT LISTS (MOCK DATA)
 * ==========================================
 */

export const BEST_OFFER_PRODUCTS: Product[] = [
  {
    id: 1,
    name: 'Acer SA100 SATAIII SSD Drive',
    category: 'SSD Drive',
    price: 30,
    rating: 5,
    image: '@/assets/marketplace/images/uploads/2022/12/acer-sa100-sataiii-1.jpg',
    inStock: true,
    isHot: true,
    sku: '5334126',
    brand: 'Asus',
    specs: { color: 'Black', size: '2.5 inch' }
  },
  {
    id: 2,
    name: 'Alogic Ultra Mini USB Reader',
    category: 'Card Readers',
    price: 50,
    rating: 4,
    image: '@/assets/marketplace/images/uploads/2022/12/alogic-ultra-mini-usb-1.jpg',
    inStock: true,
    isHot: true,
    sku: '397707',
    brand: 'Alogic',
    specs: { color: 'Silver', size: 'Mini' }
  },
  {
    id: 3,
    name: 'AMD Ryzen 5 7600X Processor',
    category: 'Processors',
    price: 299,
    rating: 5,
    image: '@/assets/marketplace/images/uploads/2022/12/amd-ryzen-5-7600x-1.jpg',
    inStock: true,
    isHot: true,
    sku: '5001622',
    brand: 'AMD',
    specs: { color: 'Grey', size: 'AM5' }
  },
  {
    id: 4,
    name: 'Apple iPad Mini 6 Wi-Fi',
    category: 'Apple Ipad',
    price: 500,
    originalPrice: 600,
    rating: 0,
    image: '@/assets/marketplace/images/uploads/2022/12/apple-ipad-mini-pink-1.jpg',
    inStock: true,
    isHot: true,
    sku: '30912',
    brand: 'Apple',
    specs: { color: 'Pink/Grey', size: '8.3 inch' }
  },
  {
    id: 5,
    name: 'Apple MacBook Pro 16" M1 Pro',
    category: 'Apple Macbook',
    price: 2999,
    rating: 5,
    image: '@/assets/marketplace/images/uploads/2022/12/apple-macbook-pro-16-silver-1.jpg',
    inStock: false,
    isHot: true,
    isNew: true,
    sku: '30876',
    brand: 'Apple',
    specs: { color: 'Space Grey/Silver', size: '16 inch' }
  }
];

export const NEW_GOODS_PRODUCTS: Product[] = [
  {
    id: 6,
    name: 'Nothing Phone 1',
    category: 'Smart Phones',
    price: 750,
    rating: 5,
    image: '@/assets/marketplace/images/uploads/2022/12/pre-order-g-pixel-7.jpg',
    inStock: true,
    isNew: true,
    sku: '2144207'
  },
  {
    id: 7,
    name: 'Acer ProDesigner PE320QK',
    category: '4K Monitors',
    price: 750,
    rating: 4,
    image: '@/assets/marketplace/images/uploads/2022/12/acer-prodesigner-pe320qk-1.jpg',
    inStock: true,
    sku: 'SKU: 2144207'
  },
  {
    id: 8,
    name: 'Ailink Aluminium Connector',
    category: 'Cables & Adapters',
    price: 40,
    rating: 3,
    image: '@/assets/marketplace/images/uploads/2022/12/ailink-aluminium-connector-1.jpg',
    inStock: true,
    sku: 'SKU: 544026'
  }
];

/**
 * ==========================================
 * CATEGORY CONFIGURATIONS
 * ==========================================
 */

export const POPULAR_CATEGORIES: Category[] = [
  { id: 1, name: 'Headsets', image: '@/assets/marketplace/images/uploads/2022/12/logitech-g735-1.jpg' },
  { id: 2, name: 'Motherboards', image: '@/assets/marketplace/images/uploads/2022/12/rtx-4090-1-opt.jpg' },
  { id: 3, name: 'Apple MacBook', image: '@/assets/marketplace/images/uploads/2022/12/apple-macbook-pro-16-silver-1.jpg' },
  { id: 4, name: 'Apple iPad', image: '@/assets/marketplace/images/uploads/2022/12/apple-ipad-mini-pink-1.jpg' },
  { id: 5, name: 'Drones', image: '@/assets/marketplace/images/uploads/2022/12/slide-2-opt.jpg' },
  { id: 6, name: 'Mirrorless', image: '@/assets/marketplace/images/uploads/2022/12/slide-3-opt.jpg' },
  { id: 7, name: 'Apple iPhone', image: '@/assets/marketplace/images/uploads/2022/12/pre-order-g-pixel-7.jpg' }
];

/**
 * ==========================================
 * MARKETING & BANNERS CONTENT
 * ==========================================
 */

export const ACCESSORIES_TAGS = [
  { label: 'Keyboards', icon: 'fas fa-keyboard' },
  { label: 'Surface Pen', icon: 'fas fa-pen' },
  { label: 'Mice', icon: 'fas fa-mouse' },
  { label: 'Headphones', icon: 'fas fa-headphones' }
];

export const ACCESSORIES_FEATURED_PRODUCT: Product = {
  id: 'ms-1',
  name: 'Acer ProDesigner PE320QK',
  category: '4K Monitors',
  price: 750,
  rating: 0,
  image: 'https://woodmart.xtemos.com/wp-content/uploads/2022/12/w-product-acer-1.png',
  inStock: true,
  sku: '2111207'
};

export const EVENT_SMALL_PRODUCTS = [
  { id: 101, name: 'Acer ProDesigner', price: 750, image: 'https://woodmart.xtemos.com/wp-content/uploads/2022/12/w-product-acer-1.png' },
  { id: 102, name: 'Acer SA100', price: 30, image: 'https://woodmart.xtemos.com/wp-content/uploads/2022/12/w-product-acer-2.png' },
  { id: 103, name: 'Ailink Aluminium', price: 40, image: 'https://woodmart.xtemos.com/wp-content/uploads/2022/12/w-product-cable-1.png' },
  { id: 104, name: 'Alogic Ultra Mini', price: 50, image: 'https://woodmart.xtemos.com/wp-content/uploads/2022/12/w-product-reader-1.png' },
  { id: 105, name: 'AMD Radeon Pro', price: 480, image: 'https://woodmart.xtemos.com/wp-content/uploads/2022/12/w-product-gpu-1.png' }
];

/**
 * ==========================================
 * ARTICLES & INFORMATIONAL CONTENT
 * ==========================================
 */

export const ARTICLES: Article[] = [
  {
    id: 1,
    title: 'Best Gaming Laptop Models',
    category: 'Gaming, Laptops',
    date: '13 Th12 2022',
    image: '@/assets/marketplace/images/uploads/2022/12/best-gaming-laptop-model-entry-header-opt.jpg',
    excerpt: 'At solmen va esser necessi far uniform grammatica, pronunciation e plu sommun paroles...'
  },
  {
    id: 2,
    title: 'How to choose a HI-FI stereo system',
    category: 'HI-FI, Sound',
    date: '13 Th12 2022',
    image: '@/assets/marketplace/images/uploads/2022/12/how-to-choose-a-hi-fi-stereo-system-entry-header-opt.jpg',
    excerpt: 'Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi...'
  }
];

export const STORES: Store[] = [
  { name: 'Broadway Store', address: '1260 Broadway, San Francisco, CA 94109' },
  { name: 'Valencia Store', address: '1501 Valencia St, San Francisco, CA 94110' },
  { name: 'Emeryville Store', address: '1034 36th St, Emeryville, CA 94608' },
  { name: 'Alameda Store', address: '1433 High St, Alameda, CA 94501' }
];
