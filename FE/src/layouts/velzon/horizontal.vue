<script setup lang="ts">
import { onMounted, watch, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useLayoutStore } from '@/stores/layout';
import NavBar from "@/components/velzon/nav-bar.vue";
import Menu from "@/components/velzon/menu.vue";
import RightBar from "@/components/velzon/right-bar.vue";
import Footer from "@/components/velzon/footer.vue";
import simplebar from "simplebar-vue";

const route = useRoute();
const layoutStore = useLayoutStore();

const layoutType = computed(() => layoutStore.layoutType);

// Apply layout settings to DOM
const applyLayoutSettings = () => {
  document.documentElement.setAttribute('data-layout', 'horizontal');
  document.documentElement.setAttribute('data-layout-style', 'default');
  document.documentElement.setAttribute('data-layout-position', layoutStore.position);
  document.documentElement.setAttribute('data-layout-width', layoutStore.layoutWidth);
  document.documentElement.setAttribute('data-topbar', layoutStore.topbar);
  document.documentElement.setAttribute('data-sidebar', layoutStore.sidebarColor);
  document.documentElement.setAttribute('data-sidebar-size', layoutStore.sidebarSize);
  document.documentElement.setAttribute('data-sidebar-image', layoutStore.sidebarImage);
  document.documentElement.setAttribute('data-preloader', layoutStore.preloader);
  document.documentElement.setAttribute('data-bs-theme', layoutStore.mode);
  document.documentElement.setAttribute('data-sidebar-visibility', layoutStore.visibility);
  document.documentElement.setAttribute('data-sidebar-user-show', layoutStore.sidebarUserProfile ? 'true' : 'false');
  document.documentElement.setAttribute('data-theme', layoutStore.layoutTheme);
};

// Watch for changes in layout store
watch(() => layoutStore.mode, () => applyLayoutSettings());
watch(() => layoutStore.sidebarColor, () => applyLayoutSettings());
watch(() => layoutStore.sidebarImage, () => applyLayoutSettings());
watch(() => layoutStore.sidebarUserProfile, () => applyLayoutSettings());
watch(() => layoutStore.layoutWidth, () => applyLayoutSettings());
watch(() => layoutStore.topbar, () => applyLayoutSettings());

const initActiveMenu = (ele: string) => {
  setTimeout(() => {
    if (document.querySelector("#navbar-nav")) {
      let a = document
        .querySelector("#navbar-nav")
        ?.querySelector('[href="' + ele + '"]');

      if (a) {
        a.classList.add("active");
        let parentCollapseDiv = a.closest(".collapse.menu-dropdown");
        if (parentCollapseDiv) {
          parentCollapseDiv.classList.add("show");
          if (parentCollapseDiv.parentElement) {
            parentCollapseDiv.parentElement.children[0].classList.add("active");
            parentCollapseDiv.parentElement.children[0].setAttribute("aria-expanded", "true");
            if (parentCollapseDiv.parentElement.closest(".collapse.menu-dropdown")) {
                const parentCollapse = parentCollapseDiv.parentElement.closest(".collapse.menu-dropdown");
                if (parentCollapse) {
                    parentCollapse.classList.add("show");
                    if (parentCollapse.previousElementSibling)
                        parentCollapse.previousElementSibling.classList.add("active");
                }
            }
          }
        }
      }
    }
  }, 1000);
};

onMounted(() => {
  applyLayoutSettings();
  initActiveMenu(route.path);

  if (document.querySelectorAll(".navbar-nav .collapse")) {
    let collapses = document.querySelectorAll(".navbar-nav .collapse");
    collapses.forEach((collapse) => {
      collapse.addEventListener("show.bs.collapse", (e) => {
        e.stopPropagation();
        let closestCollapse = collapse.parentElement?.closest(".collapse");
        if (closestCollapse) {
          let siblingCollapses = closestCollapse.querySelectorAll(".collapse");
          siblingCollapses.forEach((siblingCollapse) => {
            if (siblingCollapse.classList.contains("show")) {
              siblingCollapse.classList.remove("show");
              siblingCollapse.parentElement?.firstChild?.parentElement?.querySelector('a')?.setAttribute("aria-expanded", "false");
            }
          });
        } else {
          let getSiblings = (elem: Element) => {
            let siblings: Element[] = [];
            let sibling = elem.parentNode?.firstChild as Element | null;
            while (sibling) {
              if (sibling.nodeType === 1 && sibling !== elem) {
                siblings.push(sibling);
              }
              sibling = sibling.nextSibling as Element | null;
            }
            return siblings;
          };
          if (collapse.parentElement) {
              let siblings = getSiblings(collapse.parentElement);
              siblings.forEach((item) => {
                if (item.childNodes.length > 2) {
                  (item.firstElementChild as Element)?.setAttribute("aria-expanded", "false");
                  item.firstElementChild?.classList.remove("active");
                }
                let ids = item.querySelectorAll("*[id]");
                ids.forEach((item1: Element) => {
                  item1.classList.remove("show");
                  (item1.parentElement?.firstChild as Element)?.setAttribute("aria-expanded", "false");
                  item1.parentElement?.firstChild?.parentElement?.firstElementChild?.classList.remove("active");
                  if (item1.childNodes.length > 2) {
                    let val = item1.querySelectorAll("ul li a");
                    val.forEach((subitem: Element) => {
                      if (subitem.hasAttribute("aria-expanded"))
                        subitem.setAttribute("aria-expanded", "false");
                    });
                  }
                });
              });
          }
        }
      });
      collapse.addEventListener("hide.bs.collapse", (e) => {
        e.stopPropagation();
        let childCollapses = collapse.querySelectorAll(".collapse");
        childCollapses.forEach((childCollapse) => {
          childCollapse.classList.remove("show");
          childCollapse.parentElement?.firstChild?.parentElement?.querySelector('a')?.setAttribute("aria-expanded", "false");
        });
      });
    });
  }
});

watch(() => route.path, (newPath) => {
  initActiveMenu(newPath);
});

</script>

<template>
  <div>
    <div id="layout-wrapper">
      <NavBar />
      <!-- ========== Horizontal Top Menu ========== -->
      <div class="app-menu navbar-menu">
        <!-- LOGO (hidden in horizontal by CSS) -->
        <div class="navbar-brand-box">
          <router-link to="/" class="logo logo-dark">
            <span class="logo-sm">
              <img src="@/assets/velzon/images/logo-sm.png" alt="" height="22" />
            </span>
            <span class="logo-lg">
              <img src="@/assets/velzon/images/logo-dark.png" alt="" height="17" />
            </span>
          </router-link>
          <router-link to="/" class="logo logo-light">
            <span class="logo-sm">
              <img src="@/assets/velzon/images/logo-sm.png" alt="" height="22" />
            </span>
            <span class="logo-lg">
              <img src="@/assets/velzon/images/logo-light.png" alt="" height="17" />
            </span>
          </router-link>
        </div>
        <div id="scrollbar">
          <Menu></Menu>
        </div>
        <div class="sidebar-background"></div>
      </div>
      <!-- Vertical Overlay-->
      <div class="vertical-overlay" id="overlay"></div>
    </div>
    <!-- ============================================================== -->
    <!-- Start Page Content here -->
    <!-- ============================================================== -->

    <div class="main-content">
      <div class="page-content">
        <!-- Start Content-->
        <BContainer fluid>
          <slot />
        </BContainer>
      </div>
      <Footer />
    </div>
    <RightBar />
  </div>
</template>