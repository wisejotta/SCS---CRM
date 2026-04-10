<script setup>
import LeadDetails from '@/components/LeadDetails.vue';

const snackbar = ref({
  active: false,
  text: null,
  color: null,
})

const rowPerPage = ref(10)
const leads = ref([])

const currentPage = ref(1)
const totalPage = ref(1)
const totalLeads = ref(0)

const loading = ref(true)

const paginationData = computed(() => {
  const firstIndex = leads.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = leads.value.length + (currentPage.value - 1) * rowPerPage.value
  return `Showing ${ firstIndex } to ${ lastIndex } of ${ totalLeads.value } entries`
})

watchEffect(() => {
  if (currentPage.value > totalPage.value)
    currentPage.value = 1
})

const fetchLeads = () => {
  loading.value = true
  axios.post(`/api/admin/leads/closed`, {
    perPage: rowPerPage.value,
    currentPage: currentPage.value,
  }).then((r) => {
    leads.value = r.data.leads
    totalPage.value = r.data.totalPage
    totalLeads.value = r.data.totalLeads
  })
  .catch(e => console.log(e))
  .then(() => loading.value = false)
}
watchEffect(fetchLeads)

const isDialogVisible = ref(false)
const lead = ref(null)
const viewLead = (l = null) => {
  lead.value = l
  isDialogVisible.value = true
}

</script>

<template>
  <div>
    <VRow>
      <VCol cols="12">
        <VCard title="Closed leads">
          <VProgressLinear
            :active="loading"
            :indeterminate="loading"
            absolute
            bottom
            color="deep-purple accent-4"
          />

          <VDivider />

          <VTable class="text-no-wrap">
            <!-- 👉 table head -->
            <thead>
              <tr>
                <th scope="col">
                  FILE #
                </th>
                <th scope="col">
                  PRODUCT
                </th>
                <th scope="col">
                  AGENT
                </th>
                <th scope="col">
                  NAME
                </th>
                <th scope="col">
                  EMAIL
                </th>
                <th scope="col">
                  CELL
                </th>
                <th scope="col">
                  ACTIONS
                </th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="lead in leads"
                :key="lead.id"
                style="height: 3.75rem;"
              >
                <td
                  style="cursor: pointer;"
                >
                  <div class="d-flex align-center">
                    <div class="d-flex flex-column">
                      <h6 class="text-base">
                        <span class="font-weight-medium product-list-name">
                          {{ lead.id }}
                        </span>
                      </h6>
                    </div>
                  </div>
                </td>

                <td>
                  <span class="text-capitalize text-base">{{ lead.product }}</span>
                </td>

                <td>
                  <span class="text-capitalize text-base">{{ lead.agent }}</span>
                </td>

                <td>
                  <span class="text-base">{{ lead.name }}</span>
                </td>

                <td>
                  <span class="text-base">{{ lead.email }}</span>
                </td>

                <td>
                  <span class="text-base">{{ lead.phone_number }}</span>
                </td>

                <!-- 👉 Actions -->
                <td
                  class="text-center"
                  style="width: 5rem;"
                >
                  <VBtn
                    size="small"
                    color="primary"
                    class="mr-2"
                    @click="viewLead(lead)"
                  >
                    View Lead
                  </VBtn>
                </td>
              </tr>
            </tbody>

            <tfoot v-show="!leads.length">
              <tr>
                <td
                  colspan="7"
                  class="text-center"
                >
                  No data available
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
