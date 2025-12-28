import type { CategoryWithProducts } from '../types'

export function getCategoryThumbnail(category: CategoryWithProducts): string {
  return category.image || '/placeholder-category.jpg'
}

export function buildCategoryTree(categories: CategoryWithProducts[]): CategoryWithProducts[] {
  const map = new Map<number, CategoryWithProducts>()
  const roots: CategoryWithProducts[] = []

  categories.forEach(cat => map.set(cat.id, { ...cat, children: [] }))
  categories.forEach(cat => {
    const node = map.get(cat.id)!
    if (cat.parent_id && map.has(cat.parent_id)) {
      const parent = map.get(cat.parent_id)!
      parent.children = parent.children || []
      parent.children.push(node)
    } else {
      roots.push(node)
    }
  })

  return roots
}
