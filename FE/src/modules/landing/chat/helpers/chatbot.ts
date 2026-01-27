import type { Product } from '../../products/types/product'

/**
 * Interface for bot analysis results
 */
export interface IBotResponse {
  content: string
  identifiedProduct?: Product
}

/**
 * Analyzes guest message and returns a bot response if keywords match
 */
export function analyzeMessage(content: string, products: Product[]): IBotResponse | null {
  const normalizedContent = content.toLowerCase()

  // 1. Identify Product
  let identifiedProduct: Product | undefined
  for (const product of products) {
    if (normalizedContent.includes(product.name.toLowerCase())) {
      identifiedProduct = product
      break
    }
  }

  // 2. Keyword Matching
  
  // Price Request
  if (normalizedContent.includes('giá') || normalizedContent.includes('bao nhiêu') || normalizedContent.includes('giá cả')) {
    if (identifiedProduct) {
      const price = identifiedProduct.sale_price || identifiedProduct.price
      const formattedPrice = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
      return {
        content: `Sản phẩm **${identifiedProduct.name}** hiện có giá là **${formattedPrice}**. Bạn có muốn xem thêm chi tiết không?`,
        identifiedProduct
      }
    }
    return {
      content: 'Bạn đang quan tâm đến giá của sản phẩm nào ạ? Hãy cho mình biết tên sản phẩm nhé!'
    }
  }

  // Shipping Request
  if (normalizedContent.includes('ship') || normalizedContent.includes('vận chuyển') || normalizedContent.includes('giao hàng')) {
    return {
      content: 'Bên mình hiện đang có chương trình **Miễn phí vận chuyển** cho tất cả đơn hàng! Thời gian giao hàng dự kiến từ 2-4 ngày làm việc ạ.'
    }
  }

  // Stock/Availability
  if (normalizedContent.includes('còn hàng') || normalizedContent.includes('số lượng') || normalizedContent.includes('có sẵn')) {
    if (identifiedProduct) {
      const stock = identifiedProduct.stock_quantity || 0
      if (stock > 0) {
        return {
          content: `Vâng, sản phẩm **${identifiedProduct.name}** hiện đang còn hàng ạ! (Số lượng còn lại: ${stock}). Bạn có muốn đặt hàng ngay không?`,
          identifiedProduct
        }
      } else {
        return {
          content: `Rất tiếc, sản phẩm **${identifiedProduct.name}** hiện đang tạm hết hàng. Bạn có muốn mình thông báo khi có hàng trở lại không?`,
          identifiedProduct
        }
      }
    }
  }

  // Support/Policy
  if (normalizedContent.includes('hỗ trợ') || normalizedContent.includes('chính sách') || normalizedContent.includes('đổi trả')) {
    return {
      content: 'Bên mình hỗ trợ khách hàng **24/7**! Bạn có thể đổi trả sản phẩm trong vòng **30 ngày** nếu có lỗi từ nhà sản xuất. Bạn cần mình giúp gì thêm không?'
    }
  }

  // No match
  return null
}
