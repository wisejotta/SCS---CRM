<script setup>
import LeadDetails from '@/components/LeadDetails.vue';

const snackbar = ref({
  active: false,
  text: null,
  color: null,
})

const isDialogVisible = ref(false);
const paymentsCount = ref(0);
const lead = ref(null);
const notifications = ref([]);
const updated_at = ref(0);
const callback = ref(null);

axios
  .get('/api/leads/payments')
  .then((r) => {
    notifications.value = r.data.payments
    const payments = r.data.payments
    if(payments && payments.length) {
      updated_at.value = notifications.value[0].updated_at
    }

    setInterval(() => {
      axios
        .get(`/api/leads/payments/${updated_at.value}`)
        .then((r) => {
          const newCallback = r.data.callback;
          if(newCallback && (!callback.value || callback.value.id != newCallback.id || callback.value.minutes != newCallback.minutes)) {
              callback.value = newCallback
              snackbar.value.active = true
              snackbar.value.text = `Callback with ${newCallback.name} in ${newCallback.minutes} minutes`
          }

          const payments = r.data.payments
          if(payments && payments.length) {
            paymentsCount.value += payments.length;
            updated_at.value = payments[0].updated_at
            payments.forEach((payment, i) => notifications.value.unshift(payment))

            snackbar.value.active = true
            snackbar.value.text = 'Success!'
            snackbar.value.color = 'success'
          }
        })
        .catch(e => { console.log(e) })
    }, 10000)
  })
  .catch(e => { console.log(e) })

const viewLead = (id) => {
  lead.value = { id }
  isDialogVisible.value = true
}

</script>

<template>
  <div>
    <VBtn
      icon
      variant="text"
      color="default"
      size="small"
    >
      <VBadge
        color="error"
        :content="paymentsCount"
      >
        <VIcon
          icon="tabler-bell"
          size="24"
        />
      </VBadge>

      <VMenu
        activator="parent"
        width="380px"
        offset="14px"
      >
        <VList class="py-0">
          <!-- 👉 Header -->
          <VListItem
            title="Payments"
            class="notification-section"
            height="48px"
          >
            <template #append>
              <VChip
                v-if="paymentsCount"
                color="primary"
                size="small"
              >
                {{ paymentsCount }} New
              </VChip>
            </template>
          </VListItem>

          <VDivider />

          <!-- 👉 Notifications list -->
          <template
            v-for="payment in notifications"
          >
            <v-card
              class="blue mx-auto mb-4 ms-4 me-4 mt-4 not-card"
              :theme="!payment.read ? 'dark' : ''"
              style="background-color: rgba(211, 211, 211, .8);"
              @click="viewLead(payment.lead_number)"
            >
              <VCardText>
                <div>
                  <div class="d-flex justify-space-between mb-2">
                    <h6 class="text-base font-weight-semibold me-3">
                      {{ payment.lead_number }}
                    </h6>
                  </div>
                  <VDivider />
                  <span class="mt-4 mb-4">{{ payment.name }}</span>
                </div>
                <VDivider />

                <p class="mt-2 mb-1">
                  <span>{{ payment.message }}</span>
                </p>

                <p class="mb-2">
                  {{ payment.date }}
                </p>
              </VCardText>
            </v-card>

          </template>
        </VList>
      </VMenu>
    </VBtn>

    <VDialog
      v-model="isDialogVisible"
      fullscreen
      :scrim="false"
      transition="dialog-bottom-transition"
    >
      <LeadDetails :lead="lead" @close:dialog="isDialogVisible = false"/>
    </VDialog>

    <VSnackbar
      v-model="snackbar.active"
      :color="snackbar.color"
      :timeout="3000"
      location="top end"
      variant="flat"
    >
      {{ snackbar.text }}
    </VSnackbar>
  </div>
</template>

<style lang="scss">
.notification-section {
  padding: 14px !important;
}

.not-card {
  cursor: pointer;
}
</style>