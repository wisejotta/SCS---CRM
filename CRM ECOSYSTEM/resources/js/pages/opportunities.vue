<script setup>

import LeadDetails from '@/components/LeadDetails.vue';

const snackbar = ref({
  active: false,
  text: null,
  color: null,
})



const rowPerPage = ref(10)
const payments = ref([])

const currentPage = ref(1)
const totalPage = ref(1)
const totalPayments = ref(0)
const searchQuery = ref('')

const loading = ref(true)

const lead = ref(null)
const isDialogVisible = ref(false)

const viewLead = (id) => {
  lead.value = {id}
  isDialogVisible.value = true
}

const paginationData = computed(() => {
  const firstIndex = payments.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = payments.value.length + (currentPage.value - 1) * rowPerPage.value
  return `Showing ${ firstIndex } to ${ lastIndex } of ${ totalPayments.value } entries`
})

watchEffect(() => {
  if (currentPage.value > totalPage.value)
    currentPage.value = 1
})

let search = ''
const fetchPayments = () => {
  loading.value = true
  axios.post(`/api/payments/opportunities`, {
    perPage: rowPerPage.value,
    currentPage: currentPage.value,
    search,
  }).then((r) => {
    payments.value = r.data.payments
    totalPage.value = r.data.totalPage
    totalPayments.value = r.data.totalPayments
  })
  .catch(e => console.log(e))
  .then(() => loading.value = false)
}

watchEffect(fetchPayments)

let myTimeout
watch(searchQuery, () => {
  search = searchQuery.value
  loading.value = true

  if(myTimeout)
    clearTimeout(myTimeout)

  myTimeout = setTimeout(fetchPayments, 500)
})
</script>

<template>
  <div>
    <VRow>
      <VCol cols="12">
        <VCard title="Opportunities">

          <VCardText class="d-flex flex-wrap py-4 gap-4">
            <VRow>
              <vCol cols="12" md="4" sm="12">
                <div  class="demo-space-x">
                  <VTextField
                    v-model="searchQuery"
                    placeholder="Search"
                    clearable
                    density="compact"
                  />
                </div>
              </vCol>
            </VRow>
          </VCardText>

          <VProgressLinear
            :active="loading"
            :indeterminate="loading"
            absolute
            bottom
            color="deep-purple accent-4" />
          <VDivider />

          <VTable class="text-no-wrap">
            <!-- 👉 table head -->
            <thead>
              <tr>
                <th scope="col">
                  FILE #
                </th>
                <th scope="col">
                  CLIENT
                </th>
                <th scope="col">
                  AMOUNT
                </th>
                <th scope="col">
                  DATE
                </th>
                <th scope="col">
                  ACTIONS
                </th>
              </tr>
            </thead>
            <!-- 👉 table body -->
            <tbody>
              <tr
                v-for="payment in payments"
                :key="payment.id"
                style="height: 3.75rem;"
              >
                <td>
                  <span class="text-base">{{ payment.file_number }}</span>
                </td>

                <td>
                  <span class="text-base">{{ payment.customer }}</span>
                </td>

                <td>
                  <span class="text-base">{{ payment.amount }}</span>
                </td>

                <td>
                  <span class="text-base">{{ payment.date }}</span>
                </td>

                <!-- 👉 Actions -->
                <td
                  class="text-center"
                  style="width: 5rem;"
                >
                  <VBtn
                    class="ml-2"
                    size="small"
                    @click="viewLead(payment.file_number)"
                  >
                    VIEW LEAD
                  </VBtn>
                </td>
              </tr>
            </tbody>

            <!-- 👉 table footer  -->
            <tfoot v-show="!payments.length">
              <tr>
                <td
                  colspan="7"
                  class="text-center"
                  >
                  {{ loading ? 'Loading... Please wait' : 'No data available' }}
                </td>
              </tr>
            </tfoot>
          </VTable>

          <VDivider />

          <VCardText class="d-flex align-center flex-wrap justify-space-between gap-4 py-3 px-5">
            <span class="text-sm text-disabled">
              {{ paginationData }}
            </span>

            <VPagination
              v-model="currentPage"
              size="small"
              :total-visible="5"
              :length="totalPage"
            />
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <VDialog
      v-model="isDialogVisible"
      fullscreen
      :scrim="false"
      transition="dialog-bottom-transition"
    >
      <LeadDetails
        :lead="lead"
        @close:dialog="isDialogVisible = false"
      />
    </VDialog>

    <VSnackbar
      v-model="snackbar.active"
      :color="snackbar.color"
      :timeout="2000"
      location="top end"
      variant="flat"
    >
      {{ snackbar.text }}
    </VSnackbar>
  </div>
</template>

<style lang="scss">
  @use "@core-scss/template/pages/misc.scss";
</style>
