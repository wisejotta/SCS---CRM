<script setup>
import '@fullcalendar/core/vdom'
import CardStatisticsSalesOverview from '@/components/CardStatisticsSalesOverview.vue';
import EcommerceTotalProfitLineCharts from '@/components/EcommerceTotalProfitLineCharts.vue';
import Sales from '@/components/Sales.vue';

import {
blankEvent,
useCalendar,
} from '@/views/apps/calendar/useCalendar'
import { useResponsiveLeftSidebar } from '@core/composable/useResponsiveSidebar'
import FullCalendar from '@fullcalendar/vue3'

import LeadDetails from '@/components/LeadDetails.vue'


const emit = defineEmits([
  'close:dialog',
])

const props = defineProps({
  agent: {
    type: Object,
    required: true,
  },
})

const loading = ref(true)
const agent = ref(props.agent)

axios
  .get(`/api/agents/${props.agent.id}`)
  .then(r => agent.value = r.data)
  .catch(e => console.log(e))
  .then(() => loading.value = false)



// 👉 Event
const event = ref(structuredClone(blankEvent))
const isEventHandlerSidebarActive = ref(false)

const isLeadDialogVisible = ref(false)

watch(isEventHandlerSidebarActive, val => {
  if (!val)
    event.value = structuredClone(blankEvent)
})

const { isLeftSidebarOpen } = useResponsiveLeftSidebar()
const { refCalendar, calendarOptions } = useCalendar(event, isEventHandlerSidebarActive, isLeadDialogVisible, isLeftSidebarOpen, props.agent.id)

</script>

<template>
  <VCard
    style="background-image: url('/img/background3.png'); background-repeat: repeat;"
  >
    <div>
      <VToolbar color="primary">
        <VBtn
          icon
          variant="plain"
          @click="emit('close:dialog')"
        >
          <VIcon
            color="white"
            icon="tabler-x"
          />
        </VBtn>

        <VToolbarTitle>{{ `${agent.firstname} ${agent.lastname}` }}</VToolbarTitle>
      </VToolbar>

      <VProgressLinear
        :active="loading"
        :indeterminate="loading"
        absolute
        bottom
        color="deep-purple accent-4"
      />

      <VRow class="mt-4 ml-4 mr-4">
        <VCol cols="12" md="9">
          <VCard flat>
            <FullCalendar
              ref="refCalendar"
              :options="calendarOptions"
            />
          </VCard>

          <div v-if="!loading" class="mt-4">
            <Sales :userId="agent.id" />
          </div>
        </VCol>
        
        <VCol v-if="!loading" cols="12" md="3">
          <CardStatisticsSalesOverview :commission="agent.commission" class="mb-2"/>
          <EcommerceTotalProfitLineCharts :commission="agent.commission" />
        </VCol>
      </VRow>
    </div>

    <VDialog
      v-model="isLeadDialogVisible"
      fullscreen
      :scrim="false"
      transition="dialog-bottom-transition"
    >
      <LeadDetails :lead="event" @close:dialog="isLeadDialogVisible = false"/>
    </VDialog>

  </VCard>

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
