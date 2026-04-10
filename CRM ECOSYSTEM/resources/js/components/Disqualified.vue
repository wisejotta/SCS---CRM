<script setup>
import LeadDetails from '@/components/LeadDetails.vue';

const props = defineProps({
  type: {
    type: String,
    required: true,
  },
})

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
const searchQuery = ref('')
const removeLeadsDialogVisible = ref(false)
const removeLeadLoading = ref(false)

const loading = ref(true)

const toDlt = ref(undefined)
const removeLead = () => {
  removeLeadLoading.value = true
  axios.post('/api/leads/delete', { ids: [toDlt.value] })
    .then((r) => {
      removeLeadsDialogVisible.value = false
      snackbar.value.active = true
      snackbar.value.text = 'Lead removed!'
      snackbar.value.color = null
      fetchLeads()
    })
    .catch(e => {
      snackbar.value.active = true
      snackbar.value.text = 'Error removing lead!'
      snackbar.value.color = 'error'
      console.log(e)
    })
    .then(() => removeLeadLoading.value = false)
}

const putBack = (id) => {
  loading.value = true
  axios.post('/api/leads/put-back', { ids: [id] })
    .then((r) => {
      fetchLeads()
    })
    .catch(e => {
      loading.value = false
      snackbar.value.active = true
      snackbar.value.text = 'Error putting back lead!'
      snackbar.value.color = 'error'
      console.log(e)
    })
}

const rmvLead = (id) => {
  removeLeadsDialogVisible.value = true
  toDlt.value = id
}

const paginationData = computed(() => {
  const firstIndex = leads.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = leads.value.length + (currentPage.value - 1) * rowPerPage.value
  return `Showing ${ firstIndex } to ${ lastIndex } of ${ totalLeads.value } entries`
})

watchEffect(() => {
  if (currentPage.value > totalPage.value)
    currentPage.value = 1
})

let search = ''
const fetchLeads = () => {
  loading.value = true
  axios.post(`/api/admin/leads/disqualified/${props.type}`, {
    perPage: rowPerPage.value,
    currentPage: currentPage.value,
    search,
  }).then((r) => {
    leads.value = r.data.leads
    totalPage.value = r.data.totalPage
    totalLeads.value = r.data.totalLeads
  })
  .catch(e => console.log(e))
  .then(() => loading.value = false)
}
watchEffect(fetchLeads)

let myTimeout
watch(searchQuery, () => {
  search = searchQuery.value
  loading.value = true

  if(myTimeout)
    clearTimeout(myTimeout)

  myTimeout = setTimeout(fetchLeads, 500)
})

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
        <VCard :title="`${props.type} leads`">
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
            color="deep-purple accent-4"
          />

          <VDivider />

          <VTable class="text-no-wrap">
            <!-- 👉 table head -->
            <thead>
              <tr>
                <th scope="col">
                  ACTIONS
                </th>
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
                  DISQUALIFIED AT
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
                  class="pe-0"
                >
                  <VTooltip>
                    <template #activator="{ props }">
                      <VBtn
                        icon
                        variant="text"
                        color="default"
                        size="x-small"
                        class="me-2"
                        v-bind="props"
                        @click="viewLead(lead)"
                      >
                        <VIcon
                          :size="22"
                          icon="tabler-eye"
                        />
                      </VBtn>
                    </template>

                    <p class="mb-0">
                      View Lead
                    </p>
                  </VTooltip>

                  <VTooltip>
                    <template #activator="{ props }">
                      <VBtn
                        icon
                        variant="text"
                        color="default"
                        size="x-small"
                        v-bind="props"
                        @click="putBack(lead.id)"
                      >
                        <VIcon
                          :size="22"
                          icon="tabler-arrow-back-up"
                        />
                      </VBtn>
                    </template>

                    <p class="mb-0">
                      Restore Lead
                    </p>
                  </VTooltip>

                  <VTooltip>
                    <template #activator="{ props }">
                      <VBtn
                        icon
                        variant="text"
                        color="default"
                        size="x-small"
                        v-bind="props"
                        @click="rmvLead(lead.id)"
                      >
                        <VIcon
                          :size="22"
                          icon="tabler-trash"
                        />
                      </VBtn>
                    </template>

                    <p class="mb-0">
                      Delete Lead
                    </p>
                  </VTooltip>
                </td>
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
                  <span class="text-base">{{ lead.date }}</span>
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

    <VDialog
      v-model="removeLeadsDialogVisible"
      persistent
      class="v-dialog-sm"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="removeLeadsDialogVisible = !removeLeadsDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Remove Lead?">
        <VCardText>
          Delete selected Lead?
        </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="removeLeadsDialogVisible = false"
          >
            Close
          </VBtn>
          <VBtn
            @click="removeLead"
            :disabled="removeLeadLoading"
            :loading="removeLeadLoading"
          >
            Confirm
          </VBtn>
        </VCardText>
      </VCard>
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
