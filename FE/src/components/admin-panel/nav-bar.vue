<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useLayoutStore } from '@/stores/layout';
import { useAuthStore } from '@/stores';
import img1 from "@/assets/admin-panel/images/products/img-1.png"
import img2 from "@/assets/admin-panel/images/products/img-2.png"
import img3 from "@/assets/admin-panel/images/products/img-3.png"
import img4 from "@/assets/admin-panel/images/products/img-6.png"
import img5 from "@/assets/admin-panel/images/products/img-5.png"

import simplebar from "simplebar-vue";
import { useI18n } from 'vue-i18n';

import flagUs from "@/assets/admin-panel/images/flags/us.svg";
import flagSpain from "@/assets/admin-panel/images/flags/spain.svg";
import flagGermany from "@/assets/admin-panel/images/flags/germany.svg";
import flagItaly from "@/assets/admin-panel/images/flags/italy.svg";
import flagRussia from "@/assets/admin-panel/images/flags/russia.svg";
import flagChina from "@/assets/admin-panel/images/flags/china.svg";
import flagFrench from "@/assets/admin-panel/images/flags/french.svg";
import flagAe from "@/assets/admin-panel/images/flags/ae.svg";

const { t, locale } = useI18n();
const router = useRouter();
const layoutStore = useLayoutStore();
const authStore = useAuthStore();

// User computed values from auth store
const currentUser = computed(() => authStore.user);
const userName = computed(() => authStore.userName || 'User');
const userEmail = computed(() => authStore.user?.email || '');
const userAvatar = computed(() => (authStore.user as any)?.avatar || null);
const userRole = computed(() => {
  const user = authStore.user;
  if (!user) return '';
  if (user.roles && Array.isArray(user.roles) && user.roles.length > 0) {
    return user.roles[0].name;
  }
  return user.role || 'User';
});

// User initials for avatar fallback
const userInitials = computed(() => {
  const name = userName.value;
  if (!name) return 'U';
  const parts = name.split(' ');
  if (parts.length >= 2) {
    return parts[0][0] + parts[1][0];
  }
  return name.substring(0, 2);
});

// Logout function
const handleLogout = async () => {
  await authStore.logout();
  router.push('/');
};

const cartItems = ref([
  {
    id: 1,
    productImage: img1,
    productName: "Branded T-Shirts",
    productLink: "/ecommerce/product-details",
    quantity: "10 x $32",
    itemPrice: "320",
  },
  {
    id: 2,
    productImage: img2,
    productName: "Bentwood Chair",
    productLink: "/ecommerce/product-details",
    quantity: "5 x $18",
    itemPrice: "89",
  },
  {
    id: 3,
    productImage: img3,
    productName: "Borosil Paper Cup",
    productLink: "/ecommerce/product-details",
    quantity: "3 x $250",
    itemPrice: "750",
  },
  {
    id: 4,
    productImage: img4,
    productName: "Gray Styled T-Shirt",
    productLink: "/ecommerce/product-details",
    quantity: "1 x $1250",
    itemPrice: "1250",
  },
  {
    id: 5,
    productImage: img5,
    productName: "Stillbird Helmet",
    productLink: "/ecommerce/product-details",
    quantity: "2 x $495",
    itemPrice: "990",
  },
]);

const languages = ref([
  {
    flag: flagUs,
    language: "en",
    title: "English",
  },
  {
    flag: flagSpain,
    language: "sp",
    title: "Española",
  },
  {
    flag: flagGermany,
    language: "gr",
    title: "Deutsche",
  },
  {
    flag: flagItaly,
    language: "it",
    title: "italiana",
  },
  {
    flag: flagRussia,
    language: "ru",
    title: "русский",
  },
  {
    flag: flagChina,
    language: "ch",
    title: "中國人",
  },
  {
    flag: flagFrench,
    language: "fr",
    title: "Français",
  },
  {
    flag: flagAe,
    language: "ar",
    title: "Arabic",
  },
]);

const currentLanguage = ref(locale.value);
const text = ref<string | null>(null);
const flag = ref<string | null>(null);

const calculateTotalPrice = computed(() => {
  return cartItems.value.reduce((total, item) => total + parseFloat(item.itemPrice), 0).toFixed(2);
});

const isCustomDropdown = () => {
  const searchOptions = document.getElementById("search-close-options");
  const dropdown = document.getElementById("search-dropdown");
  const searchInput = document.getElementById("search-options") as HTMLInputElement;

  if (searchInput && dropdown && searchOptions) {
    searchInput.addEventListener("focus", () => {
      if (searchInput.value.length > 0) {
        dropdown.classList.add("show");
        searchOptions.classList.remove("d-none");
      } else {
        dropdown.classList.remove("show");
        searchOptions.classList.add("d-none");
      }
    });

    searchInput.addEventListener("keyup", () => {
      if (searchInput.value.length > 0) {
        dropdown.classList.add("show");
        searchOptions.classList.remove("d-none");
      } else {
        dropdown.classList.remove("show");
        searchOptions.classList.add("d-none");
      }
    });

    searchOptions.addEventListener("click", () => {
      searchInput.value = "";
      dropdown.classList.remove("show");
      searchOptions.classList.add("d-none");
    });

    document.body.addEventListener("click", (e: any) => {
      if (e.target.getAttribute("id") !== "search-options") {
        dropdown.classList.remove("show");
        searchOptions.classList.add("d-none");
      }
    });
  }
};

const toggleHamburgerMenu = () => {
  const windowSize = document.documentElement.clientWidth;
  const layoutType = document.documentElement.getAttribute("data-layout");
  document.documentElement.setAttribute("data-sidebar-visibility", "show");
  const visiblilityType = document.documentElement.getAttribute("data-sidebar-visibility");

  const hamburgerIcon = document.querySelector(".hamburger-icon");
  if (windowSize > 767 && hamburgerIcon) {
    hamburgerIcon.classList.toggle("open");
  }

  if (layoutType === "horizontal") {
    document.body.classList.toggle("menu");
  }

  if (visiblilityType === "show" && (layoutType === "vertical" || layoutType === "semibox")) {
    if (windowSize < 1025 && windowSize > 767) {
      document.body.classList.remove("vertical-sidebar-enable");
      document.documentElement.getAttribute("data-sidebar-size") == "sm" ?
        document.documentElement.setAttribute("data-sidebar-size", "lg") :
        document.documentElement.setAttribute("data-sidebar-size", "sm");
    } else if (windowSize > 1025) {
      document.body.classList.remove("vertical-sidebar-enable");
      document.documentElement.getAttribute("data-sidebar-size") == "lg" ?
        document.documentElement.setAttribute("data-sidebar-size", "sm") :
        document.documentElement.setAttribute("data-sidebar-size", "lg");
    } else if (windowSize <= 767) {
      document.body.classList.add("vertical-sidebar-enable");
      document.documentElement.setAttribute("data-sidebar-size", "lg");
    }
  }
};

const initFullScreen = () => {
  document.body.classList.toggle("fullscreen-enable");
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen();
  } else {
    if (document.exitFullscreen) {
      document.exitFullscreen();
    }
  }
};

const setLanguage = (localeCode: string, country: string, flagImg: string) => {
  currentLanguage.value = localeCode;
  text.value = country;
  flag.value = flagImg;
  const headerImg = document.getElementById("header-lang-img");
  if (headerImg) headerImg.setAttribute("src", flagImg);
  locale.value = localeCode;
};

const toggleDarkMode = () => {
  const currentTheme = document.documentElement.getAttribute("data-bs-theme");
  const newTheme = currentTheme === "dark" ? "light" : "dark";
  document.documentElement.setAttribute("data-bs-theme", newTheme);
  layoutStore.changeMode(newTheme);
};

const removeItem = (item: any) => {
  cartItems.value = cartItems.value.filter(i => i.id !== item.id);
};

onMounted(() => {
  document.addEventListener("scroll", () => {
    const pageTopbar = document.getElementById("page-topbar");
    if (pageTopbar) {
      document.body.scrollTop >= 50 || document.documentElement.scrollTop >= 50 ?
        pageTopbar.classList.add("topbar-shadow") :
        pageTopbar.classList.remove("topbar-shadow");
    }
  });

  const hamburgerBtn = document.getElementById("topnav-hamburger-icon");
  if (hamburgerBtn) {
    hamburgerBtn.addEventListener("click", toggleHamburgerMenu);
  }

  isCustomDropdown();
});
</script>

<template>
  <header id="page-topbar">
    <div class="layout-width">
      <div class="navbar-header">
        <div class="d-flex">
          <!-- LOGO -->
          <div class="navbar-brand-box horizontal-logo">
            <router-link to="/" class="logo logo-dark">
              <span class="logo-sm">
                <img src="@/assets/admin-panel/images/logo-sm.png" alt="" height="22" />
              </span>
              <span class="logo-lg">
                <img src="@/assets/admin-panel/images/logo-dark.png" alt="" height="17" />
              </span>
            </router-link>

            <router-link to="/" class="logo logo-light">
              <span class="logo-sm">
                <img src="@/assets/admin-panel/images/logo-sm.png" alt="" height="22" />
              </span>
              <span class="logo-lg">
                <img src="@/assets/admin-panel/images/logo-light.png" alt="" height="17" />
              </span>
            </router-link>
          </div>

          <BButton v-bind="{ variant: 'white' }" type="button"
            class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger material-shadow-none" id="topnav-hamburger-icon">
            <span class="hamburger-icon">
              <span></span>
              <span></span>
              <span></span>
            </span>
          </BButton>

          <!-- App Search-->
          <form class="app-search d-none d-md-block">
            <div class="position-relative">
              <input type="text" class="form-control" placeholder="Search..." autocomplete="off" id="search-options"
                value="" />
              <span class="mdi mdi-magnify search-widget-icon"></span>
              <span class="mdi mdi-close-circle search-widget-icon search-widget-icon-close d-none"
                id="search-close-options"></span>
            </div>
            <div class="dropdown-menu dropdown-menu-lg" id="search-dropdown">
              <simplebar data-simplebar style="max-height: 320px">
                <div class="dropdown-header">
                  <h6 class="text-overflow text-muted mb-0 text-uppercase">
                    Recent Searches
                  </h6>
                </div>

                <div class="dropdown-item bg-transparent text-wrap">
                  <router-link to="/" class="btn btn-soft-secondary btn-sm rounded-pill">how to setup <i
                      class="mdi mdi-magnify ms-1"></i></router-link>
                  <router-link to="/" class="btn btn-soft-secondary btn-sm rounded-pill">buttons <i
                      class="mdi mdi-magnify ms-1"></i></router-link>
                </div>
                <div class="dropdown-header mt-2">
                  <h6 class="text-overflow text-muted mb-1 text-uppercase">
                    Pages
                  </h6>
                </div>

                <BLink href="javascript:void(0);" class="dropdown-item notify-item">
                  <i class=" ri-bubble-chart-line align-middle fs-18 text-muted me-2"></i>
                  <span>Analytics Dashboard</span>
                </BLink>

                <BLink href="javascript:void(0);" class="dropdown-item notify-item">
                  <i class="ri-lifebuoy-line align-middle fs-18 text-muted me-2"></i>
                  <span>Help Center</span>
                </BLink>

                <BLink href="javascript:void(0);" class="dropdown-item notify-item">
                  <i class=" ri-user-settings-line align-middle fs-18 text-muted me-2"></i>
                  <span>My account settings</span>
                </BLink>

                <div class="dropdown-header mt-2">
                  <h6 class="text-overflow text-muted mb-2 text-uppercase">
                    Members
                  </h6>
                </div>

                <div class="notification-list">
                  <BLink href="javascript:void(0);" class="d-flex dropdown-item notify-item py-2">
                    <img src="@/assets/admin-panel/images/users/avatar-2.jpg" class="me-3 rounded-circle avatar-xs" alt="user-pic" />
                    <div class="flex-grow-1">
                      <h6 class="m-0">Angela Bernier</h6>
                      <span class="fs-11 mb-0 text-muted">Manager</span>
                    </div>
                  </BLink>
                  <BLink href="javascript:void(0);" class="d-flex dropdown-item notify-item py-2">
                    <img src="@/assets/admin-panel/images/users/avatar-3.jpg" class="me-3 rounded-circle avatar-xs" alt="user-pic" />
                    <div class="flex-grow-1">
                      <h6 class="m-0">David Grasso</h6>
                      <span class="fs-11 mb-0 text-muted">Web Designer</span>
                    </div>
                  </BLink>
                  <BLink href="javascript:void(0);" class="d-flex dropdown-item notify-item py-2">
                    <img src="@/assets/admin-panel/images/users/avatar-5.jpg" class="me-3 rounded-circle avatar-xs" alt="user-pic" />
                    <div class="flex-grow-1">
                      <h6 class="m-0">Mike Bunch</h6>
                      <span class="fs-11 mb-0 text-muted">React Developer</span>
                    </div>
                  </BLink>
                </div>
              </simplebar>

              <div class="text-center pt-3 pb-1">
                <router-link to="/pages/search-results" class="btn btn-primary btn-sm">View All Results <i
                    class="ri-arrow-right-line ms-1"></i></router-link>
              </div>
            </div>
          </form>
        </div>

        <div class="d-flex align-items-center">
          <BDropdown class="dropdown d-md-none topbar-head-dropdown header-item" variant="secondary" dropstart
            no-caret :offset="{ alignmentAxis: 55, crossAxis: 15, mainAxis: 0 }"
            toggle-class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle material-shadow-none show "
            menu-class="dropdown-menu-lg dropdown-menu-end p-0">
            <template #button-content>
              <i class="bx bx-search fs-22"></i>
            </template>
            <BDropdownItem aria-labelledby="page-header-search-dropdown">
              <form class="p-3">
                <div class="form-group m-0">
                  <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search ..." aria-label="Recipient's username" />
                    <BButton variant="primary" type="submit">
                      <i class="mdi mdi-magnify"></i>
                    </BButton>
                  </div>
                </div>
              </form>
            </BDropdownItem>
          </BDropdown>

          <BDropdown variant="link" no-caret class="ms-1"
            toggle-class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" menu-class="dropdown-menu-end">
            <template #button-content>
              <img id="header-lang-img" :src="flagUs" alt="Header Language" class="rounded header-lang-img">
            </template>
            <BLink href="javascript:void(0);" class="dropdown-item language py-2"
              v-for="(entry, key) in languages" :data-lang="entry.language" :title="entry.title"
              @click="setLanguage(entry.language, entry.title, entry.flag)" :key="key"
              :class="{ 'active': currentLanguage === entry.language }">
              <img :src="entry.flag" alt="user-image" class="me-2 rounded" height="18">
              <span class="align-middle">{{ entry.title }}</span>
            </BLink>
          </BDropdown>

          <BDropdown variant="link" no-caret
            toggle-class="btn btn-icon btn-topbar btn-ghost-primary rounded-circle ms-1" menu-class="p-0 dropdown-menu-end">
            <template #button-content>
              <i class="bx bx-category-alt fs-22 text-primary"></i>
            </template>
            <div class="p-3 border-top-0 dropdown-head border-start-0 border-end-0 border-bottom border dropdown-menu-lg">
              <BRow class="align-items-center">
                <BCol>
                  <h6 class="m-0 fw-semibold fs-15">Web Apps</h6>
                </BCol>
                <BCol cols="auto">
                  <BLink href="#!" class="btn btn-sm btn-soft-primary">
                    View All Apps
                    <i class="ri-arrow-right-s-line align-middle"></i>
                  </BLink>
                </BCol>
              </BRow>
            </div>

            <div class="p-2">
              <BRow class="g-0">
                <BCol>
                  <BLink class="dropdown-icon-item" href="#!">
                    <img src="@/assets/admin-panel/images/brands/github.png" alt="Github" />
                    <span>GitHub</span>
                  </BLink>
                </BCol>
                <BCol>
                  <BLink class="dropdown-icon-item" href="#!">
                    <img src="@/assets/admin-panel/images/brands/bitbucket.png" alt="bitbucket" />
                    <span>Bitbucket</span>
                  </BLink>
                </BCol>
                <BCol>
                  <BLink class="dropdown-icon-item" href="#!">
                    <img src="@/assets/admin-panel/images/brands/dribbble.png" alt="dribbble" />
                    <span>Dribbble</span>
                  </BLink>
                </BCol>
              </BRow>

              <BRow class="g-0">
                <BCol>
                  <BLink class="dropdown-icon-item" href="#!">
                    <img src="@/assets/admin-panel/images/brands/dropbox.png" alt="dropbox" />
                    <span>Dropbox</span>
                  </BLink>
                </BCol>
                <BCol>
                  <BLink class="dropdown-icon-item" href="#!">
                    <img src="@/assets/admin-panel/images/brands/mail_chimp.png" alt="mail_chimp" />
                    <span>Mail Chimp</span>
                  </BLink>
                </BCol>
                <BCol>
                  <BLink class="dropdown-icon-item" href="#!">
                    <img src="@/assets/admin-panel/images/brands/slack.png" alt="slack" />
                    <span>Slack</span>
                  </BLink>
                </BCol>
              </BRow>
            </div>
          </BDropdown>

          <BDropdown variant="link" no-caret
            class="ms-1" toggle-class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"
            menu-class="dropdown-menu-xl dropdown-menu-end p-0"
            auto-close="outside">
            <template #button-content>
              <i class="bx bx-shopping-bag fs-22"></i>
              <span
                class="position-absolute topbar-badge cartitem-badge fs-10 translate-middle badge rounded-pill bg-info">{{
                  cartItems.length }} </span>
            </template>

            <div class="p-3 border-top-0 border-start-0 dropdown-head border-end-0 border-dashed border dropdown-menu-xl">
              <BRow class="align-items-center">
                <BCol>
                  <h6 class="m-0 fs-16 fw-semibold"> My Cart</h6>
                </BCol>
                <BCol cols="auto">
                  <BBadge variant="warning" class="bg-warning-subtle text-warning fs-13"><span
                      class="cartitem-badge"> {{ cartItems.length }} </span>
                    items</BBadge>
                </BCol>
              </BRow>
            </div>
            <simplebar data-simplebar style="max-height: 300px">
              <div class="p-2">
                <div class="text-center empty-cart" id="empty-cart" v-if="cartItems.length === 0">
                  <div class="avatar-md mx-auto my-3">
                    <div class="avatar-title bg-info-subtle text-info fs-36 rounded-circle">
                      <i class="bx bx-cart"></i>
                    </div>
                  </div>
                  <h5 class="mb-3">Your Cart is Empty!</h5>
                  <router-link to="/ecommerce/products" class="btn btn-success w-md mb-3">Shop Now</router-link>
                </div>
                <div class="d-block dropdown-item dropdown-item-cart text-wrap px-3 py-2"
                  v-for="(item, index) in cartItems" :key="index">
                  <div class="d-flex align-items-center">
                    <img :src="item.productImage" class="me-3 rounded-circle avatar-sm p-2 bg-light" />
                    <div class="flex-grow-1">
                      <h6 class="mt-0 mb-1 fs-14">
                        <router-link :to="item.productLink" class="text-reset">{{ item.productName }}</router-link>
                      </h6>
                      <p class="mb-0 fs-12 text-muted">
                        Quantity: <span>{{ item.quantity }}</span>
                      </p>
                    </div>
                    <div class="px-2">
                      <h5 class="m-0 fw-normal">$<span class="cart-item-price">{{ item.itemPrice }}</span></h5>
                    </div>
                    <div class="ps-2">
                      <BButton variant="link" size="sm" class="btn-icon btn-ghost-secondary remove-item-btn"
                        @click="removeItem(item)">
                        <i class="ri-close-fill fs-16"></i>
                      </BButton>
                    </div>
                  </div>
                </div>
              </div>
            </simplebar>
            <div v-if="cartItems.length" class="p-3 border-bottom-0 border-start-0 border-end-0 border-dashed border"
              id="checkout-elem">
              <div class="d-flex justify-content-between align-items-center pb-3">
                <h5 class="m-0 text-muted">Total:</h5>
                <div class="px-2">
                  <h5 class="m-0" id="cart-item-total">${{ calculateTotalPrice }}</h5>
                </div>
              </div>

              <router-link to="/ecommerce/checkout" class="btn btn-success text-center w-100">
                Checkout
              </router-link>
            </div>
          </BDropdown>

          <div class="ms-1 header-item d-none d-sm-flex">
            <BButton type="button" variant="link" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"
              data-toggle="fullscreen" @click="initFullScreen">
              <i class="bx bx-fullscreen fs-22"></i>
            </BButton>
          </div>

          <div class="ms-1 header-item d-none d-sm-flex">
            <BButton type="button" variant="link" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle light-dark-mode"
              @click="toggleDarkMode">
              <i class="bx bx-moon fs-22"></i>
            </BButton>
          </div>

          <BDropdown variant="link" no-caret class="ms-1"
            toggle-class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"
            id="page-header-notifications-dropdown" menu-class="dropdown-menu-lg dropdown-menu-end p-0"
            auto-close="outside">
            <template #button-content>
              <i class='bx bx-bell fs-22'></i>
              <span class="position-absolute topbar-badge fs-10 translate-middle badge rounded-pill bg-danger"><span
                  class="notification-badge">3</span><span class="visually-hidden">unread
                  messages
                </span>
              </span>
            </template>
            <div class="dropdown-head bg-primary bg-pattern rounded-top dropdown-menu-lg">
              <div class="p-3">
                <BRow class="align-items-center">
                  <BCol>
                    <h6 class="m-0 fs-16 fw-semibold text-white">
                      Notifications
                    </h6>
                  </BCol>
                  <BCol cols="auto" class="dropdown-tabs">
                    <BBadge variant="light" class="bg-light text-body fs-13"> 4 New</BBadge>
                  </BCol>
                </BRow>
              </div>
            </div>
            <BTabs nav-class="dropdown-tabs nav-tab-custom bg-primary px-2 pt-2">
              <BTab title=" All (4) " class="tab-pane fade py-2 ps-2 show" id="all-noti-tab" role="tabpanel">
                <simplebar data-simplebar style="max-height: 300px" class="pe-2">
                  <div class="text-reset notification-item d-block dropdown-item position-relative">
                    <div class="d-flex">
                      <div class="avatar-xs me-3 flex-shrink-0">
                        <span class="avatar-title bg-info-subtle text-info rounded-circle fs-16">
                          <i class="bx bx-badge-check"></i>
                        </span>
                      </div>
                      <div class="flex-grow-1">
                        <BLink href="#!" class="stretched-link">
                          <h6 class="mt-0 mb-2 lh-base">
                            Your <b>Elite</b> author Graphic Optimization
                            <span class="text-secondary">reward</span> is
                            ready!
                          </h6>
                        </BLink>
                        <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                          <span><i class="mdi mdi-clock-outline"></i> Just 30 sec ago</span>
                        </p>
                      </div>
                      <div class="px-2 fs-15">
                        <input class="form-check-input" type="checkbox" />
                      </div>
                    </div>
                  </div>

                  <div class="text-reset notification-item d-block dropdown-item position-relative">
                    <div class="d-flex">
                      <img src="@/assets/admin-panel/images/users/avatar-2.jpg" class="me-3 rounded-circle avatar-xs flex-shrink-0"
                        alt="user-pic" />
                      <div class="flex-grow-1">
                        <BLink href="#!" class="stretched-link">
                          <h6 class="mt-0 mb-1 fs-13 fw-semibold">
                            Angela Bernier
                          </h6>
                        </BLink>
                        <div class="fs-13 text-muted">
                          <p class="mb-1">
                            Answered to your comment on the cash flow forecast's graph 🔔.
                          </p>
                        </div>
                        <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                          <span><i class="mdi mdi-clock-outline"></i> 48 min ago</span>
                        </p>
                      </div>
                      <div class="px-2 fs-15">
                        <input class="form-check-input" type="checkbox" />
                      </div>
                    </div>
                  </div>

                  <div class="text-reset notification-item d-block dropdown-item position-relative">
                    <div class="d-flex">
                      <div class="avatar-xs me-3 flex-shrink-0">
                        <span class="avatar-title bg-danger-subtle text-danger rounded-circle fs-16">
                          <i class="bx bx-message-square-dots"></i>
                        </span>
                      </div>
                      <div class="flex-grow-1">
                        <BLink href="#!" class="stretched-link">
                          <h6 class="mt-0 mb-2 fs-13 lh-base">
                            You have received <b class="text-success">20</b> new messages in the conversation
                          </h6>
                        </BLink>
                        <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                          <span><i class="mdi mdi-clock-outline"></i> 2 hrs
                            ago</span>
                        </p>
                      </div>
                      <div class="px-2 fs-15">
                        <input class="form-check-input" type="checkbox" />
                      </div>
                    </div>
                  </div>

                  <div class="text-reset notification-item d-block dropdown-item position-relative">
                    <div class="d-flex">
                      <img src="@/assets/admin-panel/images/users/avatar-8.jpg" class="me-3 rounded-circle avatar-xs flex-shrink-0"
                        alt="user-pic" />
                      <div class="flex-grow-1">
                        <BLink href="#!" class="stretched-link">
                          <h6 class="mt-0 mb-1 fs-13 fw-semibold">
                            Maureen Gibson
                          </h6>
                        </BLink>
                        <div class="fs-13 text-muted">
                          <p class="mb-1">
                            We talked about a project on linkedin.
                          </p>
                        </div>
                        <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                          <span><i class="mdi mdi-clock-outline"></i> 4 hrs
                            ago</span>
                        </p>
                      </div>
                      <div class="px-2 fs-15">
                        <input class="form-check-input" type="checkbox" />
                      </div>
                    </div>
                  </div>

                  <div class="my-3 text-center">
                    <BButton type="button" variant="success">
                      View All Notifications
                      <i class="ri-arrow-right-line align-middle"></i>
                    </BButton>
                  </div>
                </simplebar>
              </BTab>

              <BTab title="Messages" class="tab-pane fade py-2 ps-2" id="messages-tab" role="tabpanel"
                aria-labelledby="messages-tab">
                <simplebar data-simplebar style="max-height: 300px" class="pe-2">
                  <div class="text-reset notification-item d-block dropdown-item">
                    <div class="d-flex">
                      <img src="@/assets/admin-panel/images/users/avatar-3.jpg" class="me-3 rounded-circle avatar-xs"
                        alt="user-pic" />
                      <div class="flex-grow-1">
                        <BLink href="#!" class="stretched-link">
                          <h6 class="mt-0 mb-1 fs-13 fw-semibold">
                            James Lemire
                          </h6>
                        </BLink>
                        <div class="fs-13 text-muted">
                          <p class="mb-1">
                            We talked about a project on linkedin.
                          </p>
                        </div>
                        <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                          <span><i class="mdi mdi-clock-outline"></i> 30 min ago</span>
                        </p>
                      </div>
                      <div class="px-2 fs-15">
                        <input class="form-check-input" type="checkbox" />
                      </div>
                    </div>
                  </div>

                  <div class="text-reset notification-item d-block dropdown-item">
                    <div class="d-flex">
                      <img src="@/assets/admin-panel/images/users/avatar-2.jpg" class="me-3 rounded-circle avatar-xs"
                        alt="user-pic" />
                      <div class="flex-grow-1">
                        <BLink href="#!" class="stretched-link">
                          <h6 class="mt-0 mb-1 fs-13 fw-semibold">
                            Angela Bernier
                          </h6>
                        </BLink>
                        <div class="fs-13 text-muted">
                          <p class="mb-1">
                            Answered to your comment on the cash flow
                            forecast's graph 🔔.
                          </p>
                        </div>
                        <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                          <span><i class="mdi mdi-clock-outline"></i> 2 hrs
                            ago</span>
                        </p>
                      </div>
                      <div class="px-2 fs-15">
                        <input class="form-check-input" type="checkbox" />
                      </div>
                    </div>
                  </div>

                  <div class="text-reset notification-item d-block dropdown-item">
                    <div class="d-flex">
                      <img src="@/assets/admin-panel/images/users/avatar-6.jpg" class="me-3 rounded-circle avatar-xs"
                        alt="user-pic" />
                      <div class="flex-grow-1">
                        <BLink href="#!" class="stretched-link">
                          <h6 class="mt-0 mb-1 fs-13 fw-semibold">
                            Kenneth Brown
                          </h6>
                        </BLink>
                        <div class="fs-13 text-muted">
                          <p class="mb-1">
                            Mentionned you in his comment on 📃 invoice
                            #12501.
                          </p>
                        </div>
                        <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                          <span><i class="mdi mdi-clock-outline"></i> 10 hrs
                            ago</span>
                        </p>
                      </div>
                      <div class="px-2 fs-15">
                        <input class="form-check-input" type="checkbox" />
                      </div>
                    </div>
                  </div>

                  <div class="text-reset notification-item d-block dropdown-item">
                    <div class="d-flex">
                      <img src="@/assets/admin-panel/images/users/avatar-8.jpg" class="me-3 rounded-circle avatar-xs"
                        alt="user-pic" />
                      <div class="flex-grow-1">
                        <BLink href="#!" class="stretched-link">
                          <h6 class="mt-0 mb-1 fs-13 fw-semibold">
                            Maureen Gibson
                          </h6>
                        </BLink>
                        <div class="fs-13 text-muted">
                          <p class="mb-1">
                            We talked about a project on linkedin.
                          </p>
                        </div>
                        <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                          <span><i class="mdi mdi-clock-outline"></i> 3 days
                            ago</span>
                        </p>
                      </div>
                      <div class="px-2 fs-15">
                        <input class="form-check-input" type="checkbox" />
                      </div>
                    </div>
                  </div>

                  <div class="my-3 text-center">
                    <BButton type="button" variant="success">
                      View All Messages
                      <i class="ri-arrow-right-line align-middle"></i>
                    </BButton>
                  </div>
                </simplebar>
              </BTab>

              <BTab title="Alerts" class="p-4">
                <simplebar data-simplebar style="max-height: 300px" class="pe-2">
                  <div class="w-25 w-sm-50 pt-3 mx-auto">
                    <img src="@/assets/admin-panel/images/svg/bell.svg" class="img-fluid" alt="user-pic" />
                  </div>
                  <div class="text-center pb-5 mt-2">
                    <h6 class="fs-18 fw-semibold lh-base">
                      Hey! You have no any notifications
                    </h6>
                  </div>
                </simplebar>
              </BTab>
            </BTabs>
          </BDropdown>

          <BDropdown variant="link" class="header-item topbar-user ms-1" toggle-class="btn border-0 p-0 material-shadow-none" no-caret
            id="page-header-user-dropdown" menu-class="dropdown-menu-end"
            :offset="{ alignmentAxis: -14, crossAxis: 0, mainAxis: 0 }">
            <template #button-content>
              <span class="d-flex align-items-center">
                <!-- Avatar with initials fallback -->
                <template v-if="userAvatar">
                  <img class="rounded-circle header-profile-user" :src="userAvatar" alt="Header Avatar">
                </template>
                <template v-else>
                  <span class="avatar-xs">
                    <span class="avatar-title rounded-circle bg-primary text-white">
                      {{ userInitials.toUpperCase() }}
                    </span>
                  </span>
                </template>
                <span class="text-start ms-xl-2">
                  <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">{{ userName }}</span>
                  <span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text text-capitalize">{{ userRole }}</span>
                </span>
              </span>
            </template>
            <h6 class="dropdown-header">{{ t('t-welcome') }}, {{ userName }}!</h6>
            <router-link class="dropdown-item" to="/admin/profile">
              <i class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i>
              <span class="align-middle">{{ t('t-profile') }}</span>
            </router-link>
            <router-link class="dropdown-item" to="/admin/chat">
              <i class="mdi mdi-message-text-outline text-muted fs-16 align-middle me-1"></i>
              <span class="align-middle">{{ t('t-messages') }}</span>
            </router-link>
            <router-link class="dropdown-item" to="/admin/tasks">
              <i class="mdi mdi-calendar-check-outline text-muted fs-16 align-middle me-1"></i>
              <span class="align-middle">{{ t('t-taskboard') }}</span>
            </router-link>
            <div class="dropdown-divider"></div>
            <router-link class="dropdown-item" to="/admin/settings">
              <i class="mdi mdi-cog-outline text-muted fs-16 align-middle me-1"></i>
              <span class="align-middle">{{ t('t-settings') }}</span>
            </router-link>
            <BLink class="dropdown-item" @click="handleLogout">
              <i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i>
              <span class="align-middle">{{ t('t-logout') }}</span>
            </BLink>
          </BDropdown>
        </div>
      </div>
    </div>
  </header>
</template>
