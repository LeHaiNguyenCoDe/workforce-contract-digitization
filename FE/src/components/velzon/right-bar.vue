<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useLayoutStore } from '@/stores/layout';
import simpleBar from "simplebar-vue";

const layoutStore = useLayoutStore();
const show = ref(false);

const layoutType = computed({
  get: () => layoutStore.layoutType,
  set: (val) => layoutStore.changeLayoutType(val)
});

const mode = computed({
  get: () => layoutStore.mode,
  set: (val) => layoutStore.changeMode(val)
});

const sidebarSize = computed({
  get: () => layoutStore.sidebarSize,
  set: (val) => layoutStore.changeSidebarSize(val)
});

const layoutWidth = computed({
  get: () => layoutStore.layoutWidth,
  set: (val) => layoutStore.changeLayoutWidth(val)
});

const topbar = computed({
  get: () => layoutStore.topbar,
  set: (val) => layoutStore.changeTopbar(val)
});

const sidebarColor = computed({
  get: () => layoutStore.sidebarColor,
  set: (val) => layoutStore.changeSidebarColor(val)
});

const sidebarImage = computed({
  get: () => layoutStore.sidebarImage,
  set: (val) => layoutStore.changeSidebarImage(val)
});

const sidebarUserProfile = computed({
  get: () => layoutStore.sidebarUserProfile,
  set: (val) => layoutStore.changeSidebarUserProfile(val)
});

const sidebarView = computed({
  get: () => layoutStore.sidebarView,
  set: (val) => layoutStore.changeSidebarView(val)
});

const resetLayout = () => {
  layoutStore.$reset();
};

const topFunction = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

onMounted(() => {
  const backtoTop = document.getElementById("back-to-top");
  if (backtoTop) {
    window.addEventListener('scroll', () => {
      if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
        backtoTop.style.display = "block";
      } else {
        backtoTop.style.display = "none";
      }
    });
  }
});
</script>

<template>
  <div>
    <BButton variant="danger" @click="topFunction" class="btn-icon" id="back-to-top">
      <i class="ri-arrow-up-line"></i>
    </BButton>

    <div class="customizer-setting d-none d-md-block" @click="show = !show">
      <div class="btn-info rounded-pill shadow-lg btn btn-icon btn-lg p-2" id="mdi-cog">
        <i class="mdi mdi-spin mdi-cog-outline fs-22"></i>
      </div>
    </div>

    <BOffcanvas class="border-0" id="theme-settings-offcanvas" v-model="show" placement="end"
      header-class="d-flex align-items-center bg-primary bg-gradient p-3" body-class="p-0">
      <template #header>
        <div class="m-0 flex-grow-1">
          <h5 class="text-white m-0">Cài đặt giao diện</h5>
          <p class="text-white-50 fs-13 mb-0">Tùy chỉnh giao diện theo ý muốn</p>
        </div>
      </template>

      <simpleBar class="h-100">
        <div class="p-4">
          <!-- Layout -->
          <h6 class="mb-3 text-uppercase fw-semibold">Layout</h6>
          <div class="row g-2">
            <div class="col-4">
              <div class="form-check card-radio">
                <input id="customizer-layout01" name="customizer-layout" type="radio" value="vertical"
                  v-model="layoutType" class="form-check-input" />
                <label class="form-check-label p-0 avatar-md w-100" for="customizer-layout01">
                  <span class="d-flex gap-1 h-100">
                    <span class="flex-shrink-0">
                      <span class="bg-light d-flex h-100 flex-column gap-1 p-1">
                        <span class="d-block p-1 px-2 bg-primary-subtle rounded mb-2"></span>
                        <span class="d-block p-1 px-2 pb-0 bg-primary-subtle rounded"></span>
                        <span class="d-block p-1 px-2 pb-0 bg-primary-subtle rounded"></span>
                      </span>
                    </span>
                    <span class="flex-grow-1">
                      <span class="d-flex h-100 flex-column">
                        <span class="bg-light d-block p-1"></span>
                        <span class="bg-light d-block p-1 mt-auto"></span>
                      </span>
                    </span>
                  </span>
                </label>
              </div>
              <h5 class="fs-13 text-center mt-2">Vertical</h5>
            </div>
            <div class="col-4">
              <div class="form-check card-radio">
                <input id="customizer-layout02" name="customizer-layout" type="radio" value="horizontal"
                  v-model="layoutType" class="form-check-input" />
                <label class="form-check-label p-0 avatar-md w-100" for="customizer-layout02">
                  <span class="d-flex h-100 flex-column gap-1">
                    <span class="bg-light d-flex p-1 gap-1 align-items-center">
                      <span class="d-block p-1 bg-primary-subtle rounded me-1"></span>
                      <span class="d-block p-1 pb-0 px-2 bg-primary-subtle ms-auto rounded"></span>
                      <span class="d-block p-1 pb-0 px-2 bg-primary-subtle rounded"></span>
                    </span>
                    <span class="bg-light d-block p-1"></span>
                    <span class="bg-light d-block p-1 mt-auto"></span>
                  </span>
                </label>
              </div>
              <h5 class="fs-13 text-center mt-2">Horizontal</h5>
            </div>
            <div class="col-4">
              <div class="form-check card-radio">
                <input id="customizer-layout03" name="customizer-layout" type="radio" value="twocolumn"
                  v-model="layoutType" class="form-check-input" />
                <label class="form-check-label p-0 avatar-md w-100" for="customizer-layout03">
                  <span class="d-flex gap-1 h-100">
                    <span class="flex-shrink-0">
                      <span class="bg-light d-flex h-100 flex-column gap-1">
                        <span class="d-block p-1 bg-primary-subtle mb-2"></span>
                        <span class="d-block p-1 pb-0 bg-primary-subtle"></span>
                        <span class="d-block p-1 pb-0 bg-primary-subtle"></span>
                      </span>
                    </span>
                    <span class="flex-shrink-0">
                      <span class="bg-light d-flex h-100 flex-column gap-1 p-1">
                        <span class="d-block p-1 px-2 pb-0 bg-primary-subtle rounded"></span>
                        <span class="d-block p-1 px-2 pb-0 bg-primary-subtle rounded"></span>
                      </span>
                    </span>
                    <span class="flex-grow-1">
                      <span class="d-flex h-100 flex-column">
                        <span class="bg-light d-block p-1"></span>
                        <span class="bg-light d-block p-1 mt-auto"></span>
                      </span>
                    </span>
                  </span>
                </label>
              </div>
              <h5 class="fs-13 text-center mt-2">Two Column</h5>
            </div>
          </div>

          <!-- Color Scheme -->
          <h6 class="mt-4 mb-3 text-uppercase fw-semibold">Chế độ màu</h6>
          <div class="row g-2">
            <div class="col-4">
              <div class="form-check card-radio">
                <input id="mode-light" name="mode" type="radio" value="light" v-model="mode" class="form-check-input" />
                <label class="form-check-label p-0 avatar-md w-100" for="mode-light">
                  <span class="d-flex gap-1 h-100">
                    <span class="flex-shrink-0">
                      <span class="bg-light d-flex h-100 flex-column gap-1 p-1">
                        <span class="d-block p-1 px-2 bg-primary-subtle rounded mb-2"></span>
                        <span class="d-block p-1 px-2 pb-0 bg-primary-subtle rounded"></span>
                      </span>
                    </span>
                    <span class="flex-grow-1">
                      <span class="d-flex h-100 flex-column">
                        <span class="bg-light d-block p-1"></span>
                        <span class="bg-light d-block p-1 mt-auto"></span>
                      </span>
                    </span>
                  </span>
                </label>
              </div>
              <h5 class="fs-13 text-center mt-2">Sáng</h5>
            </div>
            <div class="col-4">
              <div class="form-check card-radio">
                <input id="mode-dark" name="mode" type="radio" value="dark" v-model="mode" class="form-check-input" />
                <label class="form-check-label p-0 avatar-md w-100 bg-dark" for="mode-dark">
                  <span class="d-flex gap-1 h-100">
                    <span class="flex-shrink-0">
                      <span class="bg-secondary-subtle d-flex h-100 flex-column gap-1 p-1">
                        <span class="d-block p-1 px-2 bg-light-subtle rounded mb-2"></span>
                        <span class="d-block p-1 px-2 pb-0 bg-light-subtle rounded"></span>
                      </span>
                    </span>
                    <span class="flex-grow-1">
                      <span class="d-flex h-100 flex-column">
                        <span class="bg-secondary-subtle d-block p-1"></span>
                        <span class="bg-secondary-subtle d-block p-1 mt-auto"></span>
                      </span>
                    </span>
                  </span>
                </label>
              </div>
              <h5 class="fs-13 text-center mt-2">Tối</h5>
            </div>
          </div>

          <!-- Sidebar Color -->
          <h6 class="mt-4 mb-3 text-uppercase fw-semibold">Màu Sidebar</h6>
          <div class="row g-2">
            <div class="col-4">
              <div class="form-check sidebar-setting card-radio">
                <input id="sidebar-color-light" name="sidebar-color" type="radio" value="light" v-model="sidebarColor" class="form-check-input" />
                <label class="form-check-label p-0 avatar-md w-100" for="sidebar-color-light">
                  <span class="d-flex gap-1 h-100">
                    <span class="flex-shrink-0">
                      <span class="bg-white border-end d-flex h-100 flex-column gap-1 p-1">
                        <span class="d-block p-1 px-2 bg-primary-subtle rounded mb-2"></span>
                        <span class="d-block p-1 px-2 pb-0 bg-primary-subtle rounded"></span>
                      </span>
                    </span>
                    <span class="flex-grow-1">
                      <span class="d-flex h-100 flex-column">
                        <span class="bg-light d-block p-1"></span>
                        <span class="bg-light d-block p-1 mt-auto"></span>
                      </span>
                    </span>
                  </span>
                </label>
              </div>
              <h5 class="fs-13 text-center mt-2">Sáng</h5>
            </div>
            <div class="col-4">
              <div class="form-check sidebar-setting card-radio">
                <input id="sidebar-color-dark" name="sidebar-color" type="radio" value="dark" v-model="sidebarColor" class="form-check-input" />
                <label class="form-check-label p-0 avatar-md w-100" for="sidebar-color-dark">
                  <span class="d-flex gap-1 h-100">
                    <span class="flex-shrink-0">
                      <span class="bg-primary d-flex h-100 flex-column gap-1 p-1">
                        <span class="d-block p-1 px-2 bg-light-subtle rounded mb-2"></span>
                        <span class="d-block p-1 px-2 pb-0 bg-light-subtle rounded"></span>
                      </span>
                    </span>
                    <span class="flex-grow-1">
                      <span class="d-flex h-100 flex-column">
                        <span class="bg-light d-block p-1"></span>
                        <span class="bg-light d-block p-1 mt-auto"></span>
                      </span>
                    </span>
                  </span>
                </label>
              </div>
              <h5 class="fs-13 text-center mt-2">Tối</h5>
            </div>
            <div class="col-4">
              <div class="form-check sidebar-setting card-radio">
                <input id="sidebar-color-gradient" name="sidebar-color" type="radio" value="gradient" v-model="sidebarColor" class="form-check-input" />
                <label class="form-check-label p-0 avatar-md w-100" for="sidebar-color-gradient">
                  <span class="d-flex gap-1 h-100">
                    <span class="flex-shrink-0">
                      <span class="bg-vertical-gradient d-flex h-100 flex-column gap-1 p-1">
                        <span class="d-block p-1 px-2 bg-light-subtle rounded mb-2"></span>
                        <span class="d-block p-1 px-2 pb-0 bg-light-subtle rounded"></span>
                      </span>
                    </span>
                    <span class="flex-grow-1">
                      <span class="d-flex h-100 flex-column">
                        <span class="bg-light d-block p-1"></span>
                        <span class="bg-light d-block p-1 mt-auto"></span>
                      </span>
                    </span>
                  </span>
                </label>
              </div>
              <h5 class="fs-13 text-center mt-2">Gradient</h5>
            </div>
          </div>

          <!-- Sidebar User Profile -->
          <h6 class="mt-4 mb-3 text-uppercase fw-semibold">Avatar người dùng Sidebar</h6>
          <div class="row g-2">
            <div class="col-6">
              <div class="form-check card-radio">
                <input id="sidebar-user-show" name="sidebar-user" type="radio" :value="true" v-model="sidebarUserProfile" class="form-check-input" />
                <label class="form-check-label p-0 avatar-md w-100" for="sidebar-user-show">
                  <span class="d-flex gap-1 h-100">
                    <span class="flex-shrink-0">
                      <span class="bg-light d-flex h-100 flex-column gap-1 p-1">
                        <span class="d-block p-1 bg-primary-subtle rounded-circle mb-1" style="width: 14px; height: 14px;"></span>
                        <span class="d-block p-1 px-2 bg-primary-subtle rounded"></span>
                      </span>
                    </span>
                    <span class="flex-grow-1">
                      <span class="d-flex h-100 flex-column">
                        <span class="bg-light d-block p-1"></span>
                      </span>
                    </span>
                  </span>
                </label>
              </div>
              <h5 class="fs-13 text-center mt-2">Hiển thị</h5>
            </div>
            <div class="col-6">
              <div class="form-check card-radio">
                <input id="sidebar-user-hide" name="sidebar-user" type="radio" :value="false" v-model="sidebarUserProfile" class="form-check-input" />
                <label class="form-check-label p-0 avatar-md w-100" for="sidebar-user-hide">
                  <span class="d-flex gap-1 h-100">
                    <span class="flex-shrink-0">
                      <span class="bg-light d-flex h-100 flex-column gap-1 p-1">
                        <span class="d-block p-1 px-2 bg-primary-subtle rounded mb-2"></span>
                        <span class="d-block p-1 px-2 pb-0 bg-primary-subtle rounded"></span>
                      </span>
                    </span>
                    <span class="flex-grow-1">
                      <span class="d-flex h-100 flex-column">
                        <span class="bg-light d-block p-1"></span>
                      </span>
                    </span>
                  </span>
                </label>
              </div>
              <h5 class="fs-13 text-center mt-2">Ẩn</h5>
            </div>
          </div>

          <!-- Sidebar Images -->
          <h6 class="mt-4 mb-3 text-uppercase fw-semibold">Hình nền Sidebar</h6>
          <div class="row g-2">
            <div class="col-4">
              <div class="form-check sidebar-setting card-radio">
                <input id="sidebar-img-none" name="sidebar-image" type="radio" value="none" v-model="sidebarImage" class="form-check-input" />
                <label class="form-check-label p-0 avatar-md w-100" for="sidebar-img-none">
                  <span class="d-flex gap-1 h-100">
                    <span class="flex-shrink-0">
                      <span class="bg-primary d-flex h-100 flex-column gap-1 p-1">
                        <span class="d-block p-1 px-2 bg-light-subtle rounded mb-2"></span>
                        <span class="d-block p-1 px-2 pb-0 bg-light-subtle rounded"></span>
                      </span>
                    </span>
                    <span class="flex-grow-1">
                      <span class="d-flex h-100 flex-column">
                        <span class="bg-light d-block p-1"></span>
                        <span class="bg-light d-block p-1 mt-auto"></span>
                      </span>
                    </span>
                  </span>
                </label>
              </div>
              <h5 class="fs-13 text-center mt-2">Không</h5>
            </div>
            <div class="col-4">
              <div class="form-check sidebar-setting card-radio">
                <input id="sidebar-img-01" name="sidebar-image" type="radio" value="img-1" v-model="sidebarImage" class="form-check-input" />
                <label class="form-check-label p-0 avatar-md w-100" for="sidebar-img-01">
                  <img src="@/assets/velzon/images/sidebar/img-1.jpg" alt="" class="avatar-md w-100 object-fit-cover" />
                </label>
              </div>
              <h5 class="fs-13 text-center mt-2">Ảnh 1</h5>
            </div>
            <div class="col-4">
              <div class="form-check sidebar-setting card-radio">
                <input id="sidebar-img-02" name="sidebar-image" type="radio" value="img-2" v-model="sidebarImage" class="form-check-input" />
                <label class="form-check-label p-0 avatar-md w-100" for="sidebar-img-02">
                  <img src="@/assets/velzon/images/sidebar/img-2.jpg" alt="" class="avatar-md w-100 object-fit-cover" />
                </label>
              </div>
              <h5 class="fs-13 text-center mt-2">Ảnh 2</h5>
            </div>
            <div class="col-4">
              <div class="form-check sidebar-setting card-radio">
                <input id="sidebar-img-03" name="sidebar-image" type="radio" value="img-3" v-model="sidebarImage" class="form-check-input" />
                <label class="form-check-label p-0 avatar-md w-100" for="sidebar-img-03">
                  <img src="@/assets/velzon/images/sidebar/img-3.jpg" alt="" class="avatar-md w-100 object-fit-cover" />
                </label>
              </div>
              <h5 class="fs-13 text-center mt-2">Ảnh 3</h5>
            </div>
            <div class="col-4">
              <div class="form-check sidebar-setting card-radio">
                <input id="sidebar-img-04" name="sidebar-image" type="radio" value="img-4" v-model="sidebarImage" class="form-check-input" />
                <label class="form-check-label p-0 avatar-md w-100" for="sidebar-img-04">
                  <img src="@/assets/velzon/images/sidebar/img-4.jpg" alt="" class="avatar-md w-100 object-fit-cover" />
                </label>
              </div>
              <h5 class="fs-13 text-center mt-2">Ảnh 4</h5>
            </div>
          </div>
        </div>
      </simpleBar>

      <template #footer>
        <BRow class="g-2 text-center border-top p-3">
          <BCol cols="12">
            <BButton variant="soft-danger" class="w-100" @click="resetLayout">
              <i class="ri-refresh-line me-1"></i> Đặt lại mặc định
            </BButton>
          </BCol>
        </BRow>
      </template>
    </BOffcanvas>
  </div>
</template>

<style scoped>
.bg-vertical-gradient {
  background: linear-gradient(to bottom, var(--vz-primary), var(--vz-secondary));
}
</style>
