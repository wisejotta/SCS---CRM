<script setup>
import '@fullcalendar/core/vdom'

// Local imports
import {
blankEvent,
useCalendar,
} from '@/views/apps/calendar/useCalendar'
import { useResponsiveLeftSidebar } from '@core/composable/useResponsiveSidebar'
import FullCalendar from '@fullcalendar/vue3'

// Components

import LeadDetails from '@/components/LeadDetails.vue'

// 👉 Event
const event = ref(structuredClone(blankEvent))
const isEventHandlerSidebarActive = ref(false)

const isLeadDialogVisible = ref(false)

watch(isEventHandlerSidebarActive, val => {
  if (!val)
    event.value = structuredClone(blankEvent)
})

const { isLeftSidebarOpen } = useResponsiveLeftSidebar()
const { refCalendar, calendarOptions } = useCalendar(event, isEventHandlerSidebarActive, isLeadDialogVisible, isLeftSidebarOpen)

</script>

<template>
  <div>
    <VCard>
      <VLayout style="z-index: 0;">
        <VMain>
          <VCard flat>
            <FullCalendar
              ref="refCalendar"
              :options="calendarOptions"
            />
          </VCard>
        </VMain>
      </VLayout>
    </VCard>

    <VDialog
      v-model="isLeadDialogVisible"
      fullscreen
      :scrim="false"
      transition="dialog-bottom-transition"
    >
      <LeadDetails :lead="event" @close:dialog="isLeadDialogVisible = false"/>
    </VDialog>
  </div>
</template>

<style lang="scss">
@use "@core-scss/template/libs/full-calendar";

.calendars-checkbox {
  .v-label {
    color: rgba(var(--v-theme-on-surface), var(--v-high-emphasis-opacity));
    opacity: var(--v-high-emphasis-opacity);
  }
}

.calendar-add-event-drawer {
  &.v-navigation-drawer {
    border-end-start-radius: 0.375rem;
    border-start-start-radius: 0.375rem;
  }
}

.calendar-date-picker {
  display: none;

  +.flatpickr-input {
    +.flatpickr-calendar.inline {
      border: none;
      box-shadow: none;

      .flatpickr-months {
        border-block-end: none;
      }
    }
  }
}
</style>

<style lang="scss" scoped>
.v-layout {
  overflow: visible !important;

  .v-card {
    overflow: visible;
  }
}
</style>
