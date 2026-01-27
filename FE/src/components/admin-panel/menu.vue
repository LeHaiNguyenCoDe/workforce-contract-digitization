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

// Check if menu item or any of its children matches current route
const isActive = (item: MenuItem): boolean => {
  if (item.to && (route.path === item.to || route.path.startsWith(item.to + '/'))) return true
  if (item.children) {
    return item.children.some(child => isActive(child))
  }
  return false
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
    // If collapsing, remove this ID and all its children from expanded list
    expandedMenuIds.value = expandedMenuIds.value.filter(id => !id.startsWith(menuId))
  } else {
    // If expanding:
    // 1. Keep all parents of the clicked menu open
    // 2. Close all menus that are NOT parents of this menu (at the same level)
    // We achieve this by finding the common ancestor and keeping only the new path branches
    
    // Simple logic: remove all IDs at the same level as the level we just opened
    // A better way: keep the path to the current menu, discard others at the same level/depth
    const currentPath = path
    expandedMenuIds.value = expandedMenuIds.value.filter(id => {
      const idPath = getMenuPath(id)
      if (!idPath) return false
      // Keep if it's a parent of the new menu
      if (currentPath.includes(id)) return true
      // Close if it has the same parent as the new menu (accordion)
      if (idPath.length === currentPath.length) {
        // Compare parents
        const idParent = idPath.slice(0, -1).join('-')
        const currentParent = currentPath.slice(0, -1).join('-')
        return idParent !== currentParent
      }
      return true
    })
    
    expandedMenuIds.value.push(menuId)
  }
}

// Ensure active menu is expanded on mount
onMounted(() => {
  const findActivePath = (items: MenuItem[]): string[] | null => {
    for (const item of items) {
      if (isActive(item)) {
        if (item.children) {
          const childPath = findActivePath(item.children)
          return childPath ? [item.id, ...childPath] : [item.id]
        }
        return [item.id]
      }
    }
    return null
  }

  const activePath = findActivePath(menuItems.value)
  if (activePath) {
    expandedMenuIds.value = activePath
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
  const findActivePath = (items: MenuItem[]): string[] | null => {
    for (const item of items) {
      if (isActive(item)) {
        if (item.children) {
          const childPath = findActivePath(item.children)
          return childPath ? [item.id, ...childPath] : [item.id]
        }
        return [item.id]
      }
    }
    return null
  }

  const activePath = findActivePath(menuItems.value)
  if (activePath) {
    // Filter out old paths that are not related to the new active path to maintain accordion
    expandedMenuIds.value = activePath
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
              :class="{ active: isActive(item) }"
              :href="`#sidebar${item.id}`"
              data-bs-toggle="collapse"
              role="button"
              :aria-expanded="isExpanded(item.id)"
              :aria-controls="`sidebar${item.id}`"
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
                      :class="{ active: isActive(child) }"
                      :href="`#sidebar${child.id}`"
                      data-bs-toggle="collapse"
                      role="button"
                      :aria-expanded="isExpanded(child.id)"
                      :aria-controls="`sidebar${child.id}`"
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
                            active-class="active"
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
                      active-class="active"
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
              active-class="active"
              :class="{ active: isActive(item) }"
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
