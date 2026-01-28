<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useLayoutStore } from '@/stores/layout'
import { useI18n } from 'vue-i18n'
import { getAdminMenus, type MenuItem } from '@/router/autoRoutes'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const layoutStore = useLayoutStore()

const layoutType = computed(() => layoutStore.layoutType)
const menuItems = computed(() => getAdminMenus())

// Currently expanded menu IDs
const expandedMenuIds = ref<string[]>([])
// Currently active menu path IDs
const currentlyActivePath = ref<string[]>([])

// Check if menu item or any of its children matches current route
const isActive = (item: MenuItem): boolean => {
  if (!item.to) {
    return item.children ? item.children.some(child => isActive(child)) : false
  }

  const currentPath = route.path
  
  // Strict matching for root admin dashboard
  if (item.to === '/admin') {
    return currentPath === '/admin' || currentPath === '/admin/'
  }

  // Exact match or sub-path match
  return currentPath === item.to || currentPath.startsWith(item.to + '/')
}

// Check if a menu group should be shown/expanded
const isExpanded = (itemId: string): boolean => {
  return expandedMenuIds.value.includes(itemId)
}

// Helper to get parents of a menu item
const getMenuPath = (menuId: string, items: MenuItem[] = menuItems.value, path: string[] = []): string[] | null => {
  for (const item of items) {
    if (item.id === menuId) return [...path, item.id]
    if (item.children) {
      const found = getMenuPath(menuId, item.children, [...path, item.id])
      if (found) return found
    }
  }
  return null
}

// Toggle menu expansion state (accordion style - only one sibling open at a time)
const toggleMenu = (menuId: string, event?: Event) => {
  if (event) event.preventDefault()

  const path = getMenuPath(menuId)
  if (!path) return

  const isCurrentlyExpanded = expandedMenuIds.value.includes(menuId)

  if (isCurrentlyExpanded) {
    // If clicking the active parent, don't collapse (keep it open for the active route)
    if (currentlyActivePath.value.includes(menuId)) return

    // If collapsing, remove this ID and all its children from expanded list
    // We need to check if each item is a descendant in the hierarchy
    expandedMenuIds.value = expandedMenuIds.value.filter(id => {
      const idPath = getMenuPath(id)
      return !idPath?.includes(menuId)
    })
  } else {
    // If expanding:
    // 1. Keep all parents of the clicked menu open
    // 2. Close all menus that are NOT parents of this menu (at the same level)
    
    const currentPath = path
    expandedMenuIds.value = expandedMenuIds.value.filter(id => {
      const idPath = getMenuPath(id)
      if (!idPath) return false
      // Keep if it's in the current path
      if (currentPath.includes(id)) return true
      
      // Close siblings (items at same depth with same parent)
      if (idPath.length === currentPath.length) {
        const idParent = idPath.slice(0, -1).join('-')
        const currentParent = currentPath.slice(0, -1).join('-')
        return idParent !== currentParent
      }
      return true
    })
    
    expandedMenuIds.value.push(menuId)
  }
}

// Find the best matching path for the current route
const findBestActivePath = (items: MenuItem[]): string[] | null => {
  let bestPath: string[] | null = null
  let maxMatchLength = -1

  const search = (currentItems: MenuItem[], currentPath: string[]): void => {
    for (const item of currentItems) {
      if (item.to) {
        if (route.path === item.to || route.path.startsWith(item.to + '/')) {
          if (item.to.length > maxMatchLength) {
            maxMatchLength = item.to.length
            bestPath = [...currentPath, item.id]
          }
        }
      }
      
      if (item.children) {
        search(item.children, [...currentPath, item.id])
      }
    }
  }

  search(items, [])
  return bestPath
}

// Ensure active menu is expanded on mount
onMounted(() => {
  const activePath = findBestActivePath(menuItems.value)
  if (activePath) {
    expandedMenuIds.value = activePath
    currentlyActivePath.value = activePath
  }

  const overlay = document.getElementById('overlay')
  if (overlay) {
    overlay.addEventListener('click', () => {
      document.body.classList.remove('vertical-sidebar-enable')
    })
  }
})

// Auto-expand menu when route changes and close sidebar on mobile
watch(() => route.path, () => {
  const activePath = findBestActivePath(menuItems.value)
  if (activePath) {
    const newExpanded = [...new Set([...expandedMenuIds.value, ...activePath])]
    expandedMenuIds.value = newExpanded
    currentlyActivePath.value = activePath
  }
  document.body.classList.remove('sidebar-enable')
})

</script>

<template>
  <BContainer fluid>
    <div id="two-column-menu"></div>

    <template v-if="['vertical', 'semibox', 'horizontal'].includes(layoutType)">
      <ul class="navbar-nav h-100" id="navbar-nav">
        <!-- Menu Title -->
        <li class="menu-title">
          <span>{{ t('admin.menuTitle') }}</span>
        </li>

        <!-- Auto-generated Menu Items -->
        <li v-for="item in menuItems" :key="item.id" class="nav-item">
          <!-- Parent with children -->
          <template v-if="item.children && item.children.length > 0">
            <BLink
              class="nav-link menu-link"
              :class="{ active: currentlyActivePath.includes(item.id) }"
              role="button"
              :aria-expanded="isExpanded(item.id)"
              @click="toggleMenu(item.id, $event)"
            >
              <i v-if="item.icon" :class="item.icon"></i>
              <span>{{ t(item.label) }}</span>
              <span v-if="item.badge" class="badge ms-auto" :class="`bg-${item.badge.variant}`">
                {{ t(item.badge.text) }}
              </span>
            </BLink>
            <div 
              class="collapse menu-dropdown" 
              :class="{ show: isExpanded(item.id) }" 
              :id="`sidebar${item.id}`"
            >
              <ul class="nav nav-sm flex-column">
                <li v-for="child in item.children" :key="child.id" class="nav-item">
                  <!-- Nested submenu (level 2) -->
                  <template v-if="child.children && child.children.length > 0">
                    <BLink
                      class="nav-link"
                      :class="{ active: currentlyActivePath.includes(child.id) }"
                      role="button"
                      :aria-expanded="isExpanded(child.id)"
                      @click="toggleMenu(child.id, $event)"
                    >
                      {{ t(child.label) }}
                    </BLink>
                    <div 
                      class="collapse menu-dropdown" 
                      :class="{ show: isExpanded(child.id) }" 
                      :id="`sidebar${child.id}`"
                    >
                      <ul class="nav nav-sm flex-column">
                        <li v-for="subChild in child.children" :key="subChild.id" class="nav-item">
                          <router-link 
                            :to="subChild.to ?? '#'" 
                            class="nav-link"
                            :class="{ active: currentlyActivePath.includes(subChild.id) }"
                          >
                            {{ t(subChild.label) }}
                            <span v-if="subChild.badge" class="badge badge-pill ms-1" :class="`bg-${subChild.badge.variant}`">
                              {{ t(subChild.badge.text) }}
                            </span>
                          </router-link>
                        </li>
                      </ul>
                    </div>
                  </template>
                  <!-- Direct link (level 1 child) -->
                  <template v-else>
                    <router-link 
                      :to="child.to ?? '#'" 
                      class="nav-link"
                      :class="{ active: currentlyActivePath.includes(child.id) }"
                    >
                      {{ t(child.label) }}
                      <span v-if="child.badge" class="badge badge-pill ms-1" :class="`bg-${child.badge.variant}`">
                        {{ t(child.badge.text) }}
                      </span>
                    </router-link>
                  </template>
                </li>
              </ul>
            </div>
          </template>
          <!-- Direct link (no children) -->
          <template v-else>
            <router-link 
              :to="item.to ?? '#'" 
              class="nav-link menu-link"
              :class="{ active: currentlyActivePath.includes(item.id) }"
            >
              <i v-if="item.icon" :class="item.icon"></i>
              <span>{{ t(item.label) }}</span>
              <span v-if="item.badge" class="badge ms-auto" :class="`bg-${item.badge.variant}`">
                {{ t(item.badge.text) }}
              </span>
            </router-link>
          </template>
        </li>
      </ul>
    </template>
  </BContainer>
</template>
