import type { Directive, DirectiveBinding } from 'vue'

interface ClickOutsideElement extends HTMLElement {
  _clickOutsideHandler?: (event: MouseEvent) => void
}

/**
 * Click outside directive
 * Triggers callback when clicking outside the element
 * 
 * @example
 * <div v-click-outside="handleClose">...</div>
 */
export const vClickOutside: Directive = {
  mounted(el: ClickOutsideElement, binding: DirectiveBinding<(event: MouseEvent) => void>) {
    el._clickOutsideHandler = (event: MouseEvent) => {
      if (!(el === event.target || el.contains(event.target as Node))) {
        binding.value(event)
      }
    }
    document.addEventListener('click', el._clickOutsideHandler)
  },
  unmounted(el: ClickOutsideElement) {
    if (el._clickOutsideHandler) {
      document.removeEventListener('click', el._clickOutsideHandler)
      delete el._clickOutsideHandler
    }
  }
}

export default vClickOutside
