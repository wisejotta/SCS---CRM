<script setup>
import { HorizontalNav } from '@layouts/components'

// import { useLayouts } from '@layouts'
import { useLayouts } from '@layouts/composable/useLayouts'

const props = defineProps({
  navItems: {
    type: null,
    required: true,
  },
})

const { y: windowScrollY } = useWindowScroll()
const { width: windowWidth } = useWindowSize()
const router = useRouter()
const shallShowPageLoading = ref(false)

router.beforeEach(() => {
  shallShowPageLoading.value = true
})
router.afterEach(() => {
  shallShowPageLoading.value = false
})

const {
  _layoutClasses: layoutClasses,
  isNavbarBlurEnabled,
} = useLayouts()
</script>

<template>
  <div
    class="layout-wrapper"
    :class="layoutClasses(windowWidth, windowScrollY)"
  >
    <div
      class="layout-navbar-and-nav-container"
      :class="isNavbarBlurEnabled && 'header-blur'"
    >
      <!-- Top navigation bar -->
      <div class="layout-navbar">
        <div class="navbar-content-container">
          <slot name="navbar" />
        </div>
      </div>
      <!-- Horizontal navigation menu -->
      <div class="layout-horizontal-nav">
        <div class="horizontal-nav-content-container">
          <HorizontalNav :nav-items="navItems" />
        </div>
      </div>
    </div>

    <main class="layout-page-content">
      <template v-if="$slots['content-loading']">
        <template v-if="shallShowPageLoading">
          <slot name="content-loading" />
        </template>
        <template v-else>
          <slot />
        </template>
      </template>
      <template v-else>
        <slot />
      </template>
    </main>

    <!-- Footer -->
    <footer class="layout-footer">
      <div class="footer-content-container">
        <slot name="footer" />
      </div>
    </footer>
  </div>
</template>

<style lang="scss">
@use "@configured-variables" as variables;
@use "@layouts/styles/placeholders";
@use "@layouts/styles/mixins";

.layout-wrapper {
  &.layout-nav-type-horizontal {
    display: flex;
    flex-direction: column;

    // Use viewport-based minimum height to keep footer anchored and avoid collapsed pages.
    min-block-size: calc(var(--vh, 1vh) * 100);

    .layout-navbar-and-nav-container {
      z-index: 1;
    }

    .layout-navbar {
      z-index: variables.$layout-horizontal-nav-layout-navbar-z-index;
      block-size: variables.$layout-horizontal-nav-navbar-height;

      // Keep sticky behavior managed by the shared navbar+nav container.
      // .layout-navbar-sticky & {
      //   @extend %layout-navbar-sticky;
      // }

      // Keep hidden behavior managed by the shared navbar+nav container.
      // .layout-navbar-hidden & {
      //   @extend %layout-navbar-hidden;
      // }
    }

    // Navbar content width
    .navbar-content-container {
      @include mixins.boxed-content;
    }

    // Fixed-height content mode
    &.layout-content-height-fixed {
      max-block-size: calc(var(--vh) * 100);

      .layout-page-content {
        overflow: hidden;

        > :first-child {
          max-block-size: 100%;
          overflow-y: auto;
        }
      }
    }

    // Footer boxed content
    .layout-footer {
      .footer-content-container {
        @include mixins.boxed-content;
      }
    }
  }

  // If both navbar & horizontal nav sticky
  &.layout-navbar-sticky.horizontal-nav-sticky {
    .layout-navbar-and-nav-container {
      position: sticky;
      inset-block-start: 0;
      will-change: transform;
    }
  }

  &.layout-navbar-hidden.horizontal-nav-hidden {
    .layout-navbar-and-nav-container {
      display: none;
    }
  }
}

// Horizontal nav container
.layout-horizontal-nav {
  z-index: variables.$layout-horizontal-nav-z-index;

  // .horizontal-nav-sticky & {
  //   width: 100%;
  //   will-change: transform;
  //   position: sticky;
  //   top: 0;
  // }

  // .horizontal-nav-hidden & {
  //   display: none;
  // }

  .horizontal-nav-content-container {
    @include mixins.boxed-content(true);
  }
}
</style>
