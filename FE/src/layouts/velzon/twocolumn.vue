<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import simplebar from "simplebar-vue";
import { useLayoutStore } from "@/stores/layout";
import NavBar from "@/components/velzon/nav-bar.vue";
import RightBar from "@/components/velzon/right-bar.vue";
import Footer from "@/components/velzon/footer.vue";

const layoutStore = useLayoutStore();
const route = useRoute();

const layoutType = computed(() => layoutStore.layoutType);
const rmenu = ref(window.localStorage.getItem('rmenu') || 'twocolumn');

// Apply layout settings to DOM
const applyLayoutSettings = () => {
  const windowSize = document.documentElement.clientWidth;
  if (windowSize < 767) {
    document.documentElement.setAttribute("data-layout", "vertical");
    rmenu.value = 'vertical';
  } else {
    document.documentElement.setAttribute('data-layout', 'twocolumn');
    rmenu.value = 'twocolumn';
  }
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

const initActiveMenu = () => {
  const pathName = route.path;
  const ul = document.getElementById("navbar-nav");
  if (ul) {
    const items = Array.from(ul.querySelectorAll("a.nav-link"));
    let activeItems = items.filter((x) => x.classList.contains("active"));
    removeActivation(activeItems);
    let matchingMenuItem = items.find((x) => {
      return x.getAttribute("href") === pathName;
    });
    if (matchingMenuItem) {
      activateParentDropdown(matchingMenuItem);
    } else {
      var id = pathName.replace("/", "");
      if (id) document.body.classList.add("twocolumn-panel");
      activateIconSidebarActive(pathName);
    }
  }
};

const updateMenu = (e: string, event: Event) => {
  document.body.classList.remove("twocolumn-panel");

  const ul = document.getElementById("navbar-nav");
  if (ul) {
    const items = Array.from(ul.querySelectorAll(".show"));
    items.forEach((item) => {
      item.classList.remove("show");
    });
  }
  const icons = document.getElementById("two-column-menu");
  if (icons) {
    const activeIcons = Array.from(
      icons.querySelectorAll(".nav-icon.active")
    );
    activeIcons.forEach((item) => {
      item.classList.remove("active");
    });
  }
  const target = document.getElementById(e);
  if (target) target.classList.add("show");
  (event.target as Element).classList.add("active");
  activateIconSidebarActive("#" + e);
};

const removeActivation = (items: Element[]) => {
  items.forEach((item) => {
    if (item.classList.contains("menu-link")) {
      if (!item.classList.contains("active")) {
        item.setAttribute("aria-expanded", "false");
      }
      if (item.nextElementSibling) item.nextElementSibling.classList.remove("show");
    }
    if (item.classList.contains("nav-link")) {
      if (item.nextElementSibling) {
        item.nextElementSibling.classList.remove("show");
      }
      item.setAttribute("aria-expanded", "false");
    }
    item.classList.remove("active");
  });
};

const activateIconSidebarActive = (id: string) => {
  var menu = document.querySelector(
    "#two-column-menu .simplebar-content-wrapper a[href='" +
    id +
    "'].nav-icon"
  );
  if (menu !== null) {
    menu.classList.add("active");
  }
};

const activateParentDropdown = (item: Element) => {
  item.classList.add("active");
  let parentCollapseDiv = item.closest(".collapse.menu-dropdown");
  if (parentCollapseDiv) {
    parentCollapseDiv.classList.add("show");
    if (parentCollapseDiv.parentElement) {
        parentCollapseDiv.parentElement.children[0].classList.add("active");
        parentCollapseDiv.parentElement.children[0].setAttribute("aria-expanded", "true");
        if (parentCollapseDiv.parentElement.closest(".collapse.menu-dropdown")) {
            const parentCollapse = parentCollapseDiv.parentElement.closest(".collapse.menu-dropdown") as Element;
            if (parentCollapse.previousElementSibling) {
                if (parentCollapse.previousElementSibling.parentElement?.closest(".collapse.menu-dropdown")) {
                    const grandparent = parentCollapse.previousElementSibling.parentElement.closest(".collapse.menu-dropdown") as Element;
                    activateIconSidebarActive("#" + grandparent.getAttribute("id"));
                    grandparent.classList.add("show");
                }
            }
            activateIconSidebarActive("#" + parentCollapse.getAttribute("id"));
            parentCollapse.classList.add("show");
            if (parentCollapse.previousElementSibling)
                parentCollapse.previousElementSibling.classList.add("active");
            return false;
        }
    }
    activateIconSidebarActive("#" + parentCollapseDiv.getAttribute("id"));
    return false;
  }
  return false;
};

onMounted(() => {
  applyLayoutSettings();
  initActiveMenu();

  window.addEventListener("resize", () => {
    if (layoutType.value == 'twocolumn') {
      var windowSize = document.documentElement.clientWidth;
      if (windowSize < 767) {
        document.documentElement.setAttribute("data-layout", "vertical");
        rmenu.value = 'vertical';
        localStorage.setItem('rmenu', 'vertical');
      } else {
        document.documentElement.setAttribute("data-layout", "twocolumn");
        rmenu.value = 'twocolumn';
        localStorage.setItem('rmenu', 'twocolumn');
        setTimeout(() => {
          initActiveMenu();
        }, 50);
      }
    }
  });

  const overlay = document.getElementById('overlay');
  if (overlay) {
    overlay.addEventListener('click', () => {
      document.body.classList.remove('vertical-sidebar-enable');
    });
  }

  // Add hover functionality for two-column icons
  const iconMenu = document.getElementById('two-column-menu');
  if (iconMenu) {
    const navIcons = iconMenu.querySelectorAll('.nav-icon');
    navIcons.forEach((icon) => {
      icon.addEventListener('mouseenter', (e) => {
        const href = icon.getAttribute('href');
        if (href) {
          const targetId = href.replace('#', '');
          // Show the corresponding menu
          const target = document.getElementById(targetId);
          if (target) {
            // First hide all other menus
            const allMenus = document.querySelectorAll('#navbar-nav .menu-dropdown');
            allMenus.forEach(menu => menu.classList.remove('show'));
            // Remove active from all icons
            navIcons.forEach(i => i.classList.remove('active'));
            // Show this menu
            target.classList.add('show');
            icon.classList.add('active');
          }
        }
      });
    });
  }

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
                  item.firstElementChild?.setAttribute("aria-expanded", "false");
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

watch(() => route.path, () => {
    document.body.classList.remove("sidebar-enable");
});

</script>

<template>
  <div id="layout-wrapper">
    <NavBar />
    <div>
      <!-- ========== App Menu ========== -->
      <div class="app-menu navbar-menu">
        <!-- LOGO -->
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
          <BButton size="sm" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover">
            <i class="ri-record-circle-line"></i>
          </BButton>
        </div>
        
        <!-- Two Column Icon Menu -->
        <div id="scrollbar" v-if="rmenu == 'twocolumn'">
          <BContainer fluid>
            <div id="two-column-menu">
              <simplebar class="twocolumn-iconview list-unstyled">
                <a class="logo"><img src="@/assets/velzon/images/logo-sm.png" alt="Logo" height="22" /></a>
                <li>
                  <a href="#sidebarDashboards" class="nav-icon" role="button"
                    @click.prevent="updateMenu('sidebarDashboards', $event)"
                    @mouseenter="updateMenu('sidebarDashboards', $event)">
                    <i class="ri-dashboard-2-line"></i>
                  </a>
                </li>
                <li>
                  <a href="#sidebarApps" class="nav-icon" role="button"
                    @click.prevent="updateMenu('sidebarApps', $event)"
                    @mouseenter="updateMenu('sidebarApps', $event)">
                    <i class="ri-apps-2-line"></i>
                  </a>
                </li>
                <li>
                  <a href="#sidebarAuth" class="nav-icon" role="button"
                    @click.prevent="updateMenu('sidebarAuth', $event)"
                    @mouseenter="updateMenu('sidebarAuth', $event)">
                    <i class="ri-account-circle-line"></i>
                  </a>
                </li>
                <li>
                  <a href="#sidebarPages" class="nav-icon" role="button"
                    @click.prevent="updateMenu('sidebarPages', $event)"
                    @mouseenter="updateMenu('sidebarPages', $event)">
                    <i class="ri-pages-line"></i>
                  </a>
                </li>
                <li>
                  <a href="#sidebarLayouts" class="nav-icon" role="button"
                    @click.prevent="updateMenu('sidebarLayouts', $event)"
                    @mouseenter="updateMenu('sidebarLayouts', $event)">
                    <i class="ri-layout-3-line"></i>
                  </a>
                </li>
                <li>
                  <a href="#sidebarUI" class="nav-icon" role="button"
                    @click.prevent="updateMenu('sidebarUI', $event)"
                    @mouseenter="updateMenu('sidebarUI', $event)">
                    <i class="ri-pencil-ruler-2-line"></i>
                  </a>
                </li>
                <li>
                  <a href="#sidebarAdvanceUI" class="nav-icon" role="button"
                    @click.prevent="updateMenu('sidebarAdvanceUI', $event)"
                    @mouseenter="updateMenu('sidebarAdvanceUI', $event)">
                    <i class="ri-stack-line"></i>
                  </a>
                </li>
                <li>
                  <a href="#sidebarForms" class="nav-icon" role="button"
                    @click.prevent="updateMenu('sidebarForms', $event)"
                    @mouseenter="updateMenu('sidebarForms', $event)">
                    <i class="ri-file-list-3-line"></i>
                  </a>
                </li>
                <li>
                  <a href="#sidebarTables" class="nav-icon" role="button"
                    @click.prevent="updateMenu('sidebarTables', $event)"
                    @mouseenter="updateMenu('sidebarTables', $event)">
                    <i class="ri-layout-grid-line"></i>
                  </a>
                </li>
                <li>
                  <a href="#sidebarCharts" class="nav-icon" role="button"
                    @click.prevent="updateMenu('sidebarCharts', $event)"
                    @mouseenter="updateMenu('sidebarCharts', $event)">
                    <i class="ri-pie-chart-line"></i>
                  </a>
                </li>
                <li>
                  <a href="#sidebarIcons" class="nav-icon" role="button"
                    @click.prevent="updateMenu('sidebarIcons', $event)"
                    @mouseenter="updateMenu('sidebarIcons', $event)">
                    <i class="ri-compasses-2-line"></i>
                  </a>
                </li>
                <li>
                  <a href="#sidebarMaps" class="nav-icon" role="button"
                    @click.prevent="updateMenu('sidebarMaps', $event)"
                    @mouseenter="updateMenu('sidebarMaps', $event)">
                    <i class="ri-map-pin-line"></i>
                  </a>
                </li>
                <li>
                  <a href="#sidebarMultilevel" class="nav-icon" role="button"
                    @click.prevent="updateMenu('sidebarMultilevel', $event)"
                    @mouseenter="updateMenu('sidebarMultilevel', $event)">
                    <i class="ri-share-line"></i>
                  </a>
                </li>
              </simplebar>
            </div>
            <simplebar class="navbar-nav" id="navbar-nav">
              <li class="menu-title">
                <span data-key="t-menu">{{ $t("t-menu") }}</span>
              </li>
              <!-- Dashboards -->
              <li class="nav-item">
                <div class="collapse menu-dropdown" id="sidebarDashboards">
                  <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                      <router-link to="/dashboard/analytics" class="nav-link" data-key="t-analytics">
                        {{ $t("t-analytics") }}
                      </router-link>
                    </li>
                    <li class="nav-item">
                      <router-link to="/dashboard/crm" class="nav-link" data-key="t-crm">
                        {{ $t("t-crm") }}
                      </router-link>
                    </li>
                    <li class="nav-item">
                      <router-link to="/" class="nav-link" data-key="t-ecommerce">
                        {{ $t("t-ecommerce") }}
                      </router-link>
                    </li>
                    <li class="nav-item">
                      <router-link to="/dashboard/crypto" class="nav-link" data-key="t-crypto">
                        {{ $t("t-crypto") }}
                      </router-link>
                    </li>
                    <li class="nav-item">
                      <router-link to="/dashboard/projects" class="nav-link" data-key="t-projects">
                        {{ $t("t-projects") }}
                      </router-link>
                    </li>
                    <li class="nav-item">
                      <router-link to="/dashboard/nft" class="nav-link" data-key="t-nft">
                        {{ $t("t-nft") }}
                      </router-link>
                    </li>
                    <li class="nav-item">
                      <router-link to="/dashboard/job" class="nav-link" data-key="t-job">
                        {{ $t("t-job") }}
                      </router-link>
                    </li>
                  </ul>
                </div>
              </li>
              <!-- Apps -->
              <li class="nav-item">
                <div class="collapse menu-dropdown" id="sidebarApps">
                  <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                      <router-link to="/chat" class="nav-link" data-key="t-chat">
                        {{ $t("t-chat") }}
                      </router-link>
                    </li>
                    <li class="nav-item">
                      <router-link to="/mailbox" class="nav-link" data-key="t-mailbox">
                        {{ $t("t-mailbox") }}
                      </router-link>
                    </li>
                    <li class="nav-item">
                      <router-link to="/ecommerce/products" class="nav-link" data-key="t-products">
                        {{ $t("t-products") }}
                      </router-link>
                    </li>
                    <li class="nav-item">
                      <router-link to="/ecommerce/orders" class="nav-link" data-key="t-orders">
                        {{ $t("t-orders") }}
                      </router-link>
                    </li>
                    <li class="nav-item">
                      <router-link to="/apps-file-manager" class="nav-link" data-key="t-file-manager">
                        {{ $t("t-file-manager") }}
                      </router-link>
                    </li>
                    <li class="nav-item">
                      <router-link to="/apps-todo" class="nav-link" data-key="t-to-do">
                        {{ $t("t-to-do") }}
                      </router-link>
                    </li>
                  </ul>
                </div>
              </li>
              <!-- Auth -->
              <li class="nav-item">
                <div class="collapse menu-dropdown" id="sidebarAuth">
                  <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                      <router-link to="/auth/signin-basic" class="nav-link" data-key="t-signin">
                        {{ $t("t-signin") }}
                      </router-link>
                    </li>
                    <li class="nav-item">
                      <router-link to="/auth/signup-basic" class="nav-link" data-key="t-signup">
                        {{ $t("t-signup") }}
                      </router-link>
                    </li>
                    <li class="nav-item">
                      <router-link to="/auth/reset-pwd-basic" class="nav-link" data-key="t-password-reset">
                        {{ $t("t-password-reset") }}
                      </router-link>
                    </li>
                    <li class="nav-item">
                      <router-link to="/auth/lockscreen-basic" class="nav-link" data-key="t-lock-screen">
                        {{ $t("t-lock-screen") }}
                      </router-link>
                    </li>
                    <li class="nav-item">
                      <router-link to="/auth/logout-basic" class="nav-link" data-key="t-logout">
                        {{ $t("t-logout") }}
                      </router-link>
                    </li>
                    <li class="nav-item">
                      <router-link to="/auth/404" class="nav-link" data-key="t-404">
                        {{ $t("t-404") }}
                      </router-link>
                    </li>
                  </ul>
                </div>
              </li>
              <!-- Pages -->
              <li class="nav-item">
                <div class="collapse menu-dropdown" id="sidebarPages">
                  <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                      <router-link to="/pages/starter" class="nav-link" data-key="t-starter">
                        {{ $t("t-starter") }}
                      </router-link>
                    </li>
                    <li class="nav-item">
                      <router-link to="/pages/profile" class="nav-link" data-key="t-profile">
                        {{ $t("t-profile") }}
                      </router-link>
                    </li>
                    <li class="nav-item">
                      <router-link to="/pages/team" class="nav-link" data-key="t-team">
                        {{ $t("t-team") }}
                      </router-link>
                    </li>
                    <li class="nav-item">
                      <router-link to="/pages/timeline" class="nav-link" data-key="t-timeline">
                        {{ $t("t-timeline") }}
                      </router-link>
                    </li>
                    <li class="nav-item">
                      <router-link to="/pages/faqs" class="nav-link" data-key="t-faqs">
                        {{ $t("t-faqs") }}
                      </router-link>
                    </li>
                    <li class="nav-item">
                      <router-link to="/pages/pricing" class="nav-link" data-key="t-pricing">
                        {{ $t("t-pricing") }}
                      </router-link>
                    </li>
                  </ul>
                </div>
              </li>
              <!-- Layouts -->
              <li class="nav-item">
                <div class="collapse menu-dropdown" id="sidebarLayouts">
                  <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                      <router-link to="/" class="nav-link" data-key="t-horizontal">
                        {{ $t("t-horizontal") }}
                      </router-link>
                    </li>
                  </ul>
                </div>
              </li>
              <!-- UI Elements -->
              <li class="nav-item">
                <div class="collapse menu-dropdown" id="sidebarUI">
                  <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                      <router-link to="/ui/alerts" class="nav-link" data-key="t-alerts">
                        {{ $t("t-alerts") }}
                      </router-link>
                    </li>
                    <li class="nav-item">
                      <router-link to="/ui/badges" class="nav-link" data-key="t-badges">
                        {{ $t("t-badges") }}
                      </router-link>
                    </li>
                    <li class="nav-item">
                      <router-link to="/ui/buttons" class="nav-link" data-key="t-buttons">
                        {{ $t("t-buttons") }}
                      </router-link>
                    </li>
                    <li class="nav-item">
                      <router-link to="/ui/colors" class="nav-link" data-key="t-colors">
                        {{ $t("t-colors") }}
                      </router-link>
                    </li>
                    <li class="nav-item">
                      <router-link to="/ui/cards" class="nav-link" data-key="t-cards">
                        {{ $t("t-cards") }}
                      </router-link>
                    </li>
                  </ul>
                </div>
              </li>
              <!-- Advance UI -->
              <li class="nav-item">
                <div class="collapse menu-dropdown" id="sidebarAdvanceUI">
                  <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                      <router-link to="/advance-ui/sweetalerts" class="nav-link" data-key="t-sweet-alerts">
                        {{ $t("t-sweet-alerts") }}
                      </router-link>
                    </li>
                  </ul>
                </div>
              </li>
              <!-- Forms -->
              <li class="nav-item">
                <div class="collapse menu-dropdown" id="sidebarForms">
                  <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                      <router-link to="/forms/basic-elements" class="nav-link" data-key="t-basic-elements">
                        {{ $t("t-basic-elements") }}
                      </router-link>
                    </li>
                  </ul>
                </div>
              </li>
              <!-- Tables -->
              <li class="nav-item">
                <div class="collapse menu-dropdown" id="sidebarTables">
                  <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                      <router-link to="/tables/basic" class="nav-link" data-key="t-basic-tables">
                        {{ $t("t-basic-tables") }}
                      </router-link>
                    </li>
                  </ul>
                </div>
              </li>
              <!-- Charts -->
              <li class="nav-item">
                <div class="collapse menu-dropdown" id="sidebarCharts">
                  <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                      <router-link to="/charts/apex-line" class="nav-link" data-key="t-line">
                        {{ $t("t-line") }}
                      </router-link>
                    </li>
                  </ul>
                </div>
              </li>
              <!-- Icons -->
              <li class="nav-item">
                <div class="collapse menu-dropdown" id="sidebarIcons">
                  <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                      <router-link to="/icons/remix" class="nav-link" data-key="t-remix">
                        {{ $t("t-remix") }}
                      </router-link>
                    </li>
                  </ul>
                </div>
              </li>
              <!-- Maps -->
              <li class="nav-item">
                <div class="collapse menu-dropdown" id="sidebarMaps">
                  <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                      <router-link to="/maps/google" class="nav-link" data-key="t-google">
                        {{ $t("t-google") }}
                      </router-link>
                    </li>
                  </ul>
                </div>
              </li>
              <!-- Multilevel -->
              <li class="nav-item">
                <div class="collapse menu-dropdown" id="sidebarMultilevel">
                  <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                      <a class="nav-link" data-key="t-level-1.1">
                        {{ $t("t-level-1.1") }}
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
            </simplebar>
          </BContainer>
        </div>
        
        <!-- Fallback to Vertical Menu for small screens -->
        <simplebar id="scrollbar" class="h-100" v-else>
          <BContainer fluid>
            <ul class="navbar-nav h-100" id="navbar-nav">
              <li class="menu-title">
                <span data-key="t-menu">{{ $t("t-menu") }}</span>
              </li>
              <li class="nav-item">
                <a class="nav-link menu-link" href="#sidebarDashboards" data-bs-toggle="collapse" role="button"
                  aria-expanded="false" aria-controls="sidebarDashboards">
                  <i class="ri-dashboard-2-line"></i>
                  <span data-key="t-dashboards">{{ $t("t-dashboards") }}</span>
                </a>
                <div class="collapse menu-dropdown" id="sidebarDashboardsV">
                  <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                      <router-link to="/dashboard/analytics" class="nav-link" data-key="t-analytics">
                        {{ $t("t-analytics") }}
                      </router-link>
                    </li>
                    <li class="nav-item">
                      <router-link to="/" class="nav-link" data-key="t-ecommerce">
                        {{ $t("t-ecommerce") }}
                      </router-link>
                    </li>
                  </ul>
                </div>
              </li>
              <li class="nav-item">
                <a class="nav-link menu-link" href="#sidebarApps" data-bs-toggle="collapse" role="button"
                  aria-expanded="false" aria-controls="sidebarApps">
                  <i class="ri-apps-2-line"></i>
                  <span data-key="t-apps">{{ $t("t-apps") }}</span>
                </a>
                <div class="collapse menu-dropdown" id="sidebarAppsV">
                  <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                      <router-link to="/chat" class="nav-link" data-key="t-chat">
                        {{ $t("t-chat") }}
                      </router-link>
                    </li>

                  </ul>
                </div>
              </li>
            </ul>
          </BContainer>
        </simplebar>
        
        <div class="sidebar-background"></div>
      </div>
      <!-- Left Sidebar End -->
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