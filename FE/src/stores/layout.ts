import { defineStore } from 'pinia';

export const useLayoutStore = defineStore('layout', {
  state: () => ({
    layoutType: 'vertical',
    layoutWidth: 'fluid',
    sidebarSize: 'lg',
    topbar: 'light',
    mode: 'light',
    position: 'fixed',
    sidebarView: 'default',
    sidebarColor: 'dark',
    sidebarImage: 'none',
    sidebarUserProfile: true,
    preloader: 'disable',
    visibility: 'show',
    layoutTheme: 'default',
    themeColor: 'default',
    isMarketplaceSidebarOpen: false,
    isMarketplaceCartOpen: false,
    isMarketplaceUserOpen: false
  }),
  actions: {
    changeLayoutType(layoutType: string) {
      this.layoutType = layoutType;
      document.body.removeAttribute("style");
    },
    changeLayoutWidth(layoutWidth: string) {
      this.layoutWidth = layoutWidth;
    },
    changeSidebarSize(sidebarSize: string) {
      this.sidebarSize = sidebarSize;
    },
    changeTopbar(topbar: string) {
      this.topbar = topbar;
    },
    changeMode(mode: string) {
      this.mode = mode;
    },
    changePosition(position: string) {
      this.position = position;
    },
    changeSidebarView(sidebarView: string) {
      this.sidebarView = sidebarView;
    },
    changeSidebarColor(sidebarColor: string) {
      this.sidebarColor = sidebarColor;
    },
    changeSidebarImage(sidebarImage: string) {
      this.sidebarImage = sidebarImage;
    },
    changeSidebarUserProfile(sidebarUserProfile: boolean) {
      this.sidebarUserProfile = sidebarUserProfile;
    },
    changePreloader(preloader: string) {
      this.preloader = preloader;
    },
    changeVisibility(visibility: string) {
      this.visibility = visibility;
    },
    changeThemes(layoutTheme: string) {
      this.layoutTheme = layoutTheme;
    },
    changeThemesColor(themeColor: string) {
      this.themeColor = themeColor;
    },
    toggleMarketplaceSidebar() {
      this.isMarketplaceSidebarOpen = !this.isMarketplaceSidebarOpen;
    },
    toggleMarketplaceCart() {
      this.isMarketplaceCartOpen = !this.isMarketplaceCartOpen;
    },
    toggleMarketplaceUser() {
      this.isMarketplaceUserOpen = !this.isMarketplaceUserOpen;
    }
  }
});
