<script setup>

import CustomerDetails from '@/components/CustomerDetails.vue';
import LeadDetails from '@/components/LeadDetails.vue';

const snackbar = ref({
  active: false,
  text: null,
  color: null,
})

const agents = ref([])

axios.get('/api/admin/agents').then((r) => agents.value = r.data)

const rowPerPage = ref(10)
const searchQuery = ref('')
const leads = ref([])

const currentPage = ref(1)
const totalPage = ref(1)
const totalLeads = ref(0)

const loading = ref(false)

const recallSwitch = ref(false)

const assignAgentDialogVisible = ref(false)
const selectedAgent = ref(null)
const selectedRows = ref([])
const removeAgentDialogVisible = ref(false)
const selectedAgentLoading = ref(false)
const selectAllLeads = ref(false)

const assignLeadsPopUp = () => {
  assignAgentDialogVisible.value = true
  selectedAgent.value = null
}

const assignAgents = () => {
  selectedAgentLoading.value = true
  axios.post(`/api/admin/agents/${selectedAgent.value}/1`, {
    ids: selectedRows.value
  }).then((r) => {
    assignAgentDialogVisible.value = false
    snackbar.value.active = true
    snackbar.value.text = 'Agent assinged!'
    snackbar.value.color = null
    fetchLeads()
  })
  .catch(e => {
    snackbar.value.active = true
    snackbar.value.text = 'Error assinging the agent!'
    snackbar.value.color = 'error'
    console.log(e)
  })
  .then(() => selectedAgentLoading.value = false)
}

const removeAgents = () => {
  selectedAgentLoading.value = true
  axios.delete('/api/admin/agents', {
    data: { ids: selectedRows.value }
  }).then((r) => {
    removeAgentDialogVisible.value = false
    snackbar.value.active = true
    snackbar.value.text = 'Agents removed!'
    snackbar.value.color = null
    fetchLeads()
  })
  .catch(e => {
    snackbar.value.active = true
    snackbar.value.text = 'Error removing agents!'
    snackbar.value.color = 'error'
    console.log(e)
  })
  .then(() => selectedAgentLoading.value = false)
}

const selectUnselectAll = () => {
  selectAllLeads.value = !selectAllLeads.value
  if (selectAllLeads.value) {
    leads.value.forEach(lead => {
      if (!selectedRows.value.includes(lead.id))
        selectedRows.value.push(lead.id)
    })
  } else {
    selectedRows.value = []
  }
}

// 👉 watch if checkbox array is empty all checkbox should be uncheck
watch(selectedRows, () => {
  if (!selectedRows.value.length)
    selectAllLeads.value = false
}, { deep: true })

const addRemoveIndividualCheckbox = checkID => {
  if (selectedRows.value.includes(checkID)) {
    const index = selectedRows.value.indexOf(checkID)
    selectedRows.value.splice(index, 1)
  } else {
    selectedRows.value.push(checkID)
    selectAllLeads.value = true
  }
}

const paginationData = computed(() => {
  const firstIndex = leads.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = leads.value.length + (currentPage.value - 1) * rowPerPage.value
  return `Showing ${ firstIndex } to ${ lastIndex } of ${ totalLeads.value } entries`
})
const selectedAgents = ref([])

watchEffect(() => {
  if (currentPage.value > totalPage.value)
    currentPage.value = 1
})

let search = ''
const fetchLeads = () => {
  loading.value = true
  selectedRows.value = []
  axios.post(`/api/admin/leads/assigned`, {
    perPage: rowPerPage.value,
    currentPage: currentPage.value,
    q: search,
    selectedAgents: selectedAgents.value,
    recallsOnly: recallSwitch.value,
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
watch(searchQuery, async () => {
  search = searchQuery.value
  loading.value = true
  if(myTimeout)
    clearTimeout(myTimeout)
  myTimeout = setTimeout(async () => fetchLeads(), 500)
})

const isDialogVisible = ref(false)
const lead = ref(null)
const updateLead = (l = null) => {
  lead.value = l
  isDialogVisible.value = true
}

const isDialogVisible2 = ref(false)
const viewLead = (id) => {
  lead.value = {id}
  isDialogVisible2.value = true
}

</script>

<template>
  <div>
    <VRow>
      <VCol cols="12">
        <VCard title="Filter Leads">
          <VCardText class="d-flex flex-wrap gap-4">
            <VRow>
              <VCol cols="12">
                <div class="demo-space-x">
                  <VBtn
                      color="warning"
                      @click="removeAgentDialogVisible = true"
                      :disabled="selectedRows.length == 0"
                      size="small"
                    >
                      Unassign
                  </VBtn>

                  <VBtn
                      color="primary"
                      @click="assignLeadsPopUp"
                      :disabled="selectedRows.length == 0"
                      size="small"
                    >
                      Assign
                  </VBtn>
                </div>
              </vCol>

              <vCol cols="12" md="8" sm="12">
                <div class="demo-space-x mb-2">
                  <div style="width: 80px;">
                    <VSelect
                      v-model="rowPerPage"
                      density="compact"
                      variant="outlined"
                      :items="[5, 10, 20, 30, 50]"
                    />
                  </div>

                  <VSelect
                    v-model="selectedAgents"
                    label="Agent"
                    :items="agents"
                    item-title="name"
                    item-value="id"
                    clearable
                    multiple
                    clear-icon="tabler-x"
                  />

                  <VTextField
                    v-model="searchQuery"
                    placeholder="Search"
                    density="compact"
                  />
                </div>

                <VSwitch
                  v-model="recallSwitch"
                  label="Show re-calls only"
                />
              </vCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" md="12">

        <VCard>
          <VCardText
            style="padding: 0%;"
          >
            <VTable class="text-no-wrap">
              <thead>
                <tr>
                  <th
                    scope="col"
                    class="text-center"
                  >
                    <div style="width: 1rem;">
                      <VCheckbox
                        :model-value="selectAllLeads"
                        :indeterminate="(leads.length !== selectedRows.length) && !!selectedRows.length"
                        @click="selectUnselectAll"
                      />
                    </div>
                  </th>
                  <th>ACTIONS</th>
                  <th scope="col">
                    FILE #
                  </th>
                  <th scope="col">
                    COUNTRY
                  </th>
                  <th scope="col">
                    PROFESSION
                  </th>
                  <th scope="col">
                    AGENT
                  </th>
                  <th scope="col">
                    BO AGENT
                  </th>
                  <th scope="col">
                    PRODUCT
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
                    CREATED AT
                  </th>
                </tr>
              </thead>

              <tbody>
                <tr
                  v-for="lead in leads"
                  :key="lead.id"
                  style="height: 3.75rem;"
                >
                  <td>
                    <div style="width: 1rem;">
                      <VCheckbox
                        :id="lead.id"
                        :model-value="selectedRows.includes(lead.id)"
                        @click="addRemoveIndividualCheckbox(lead.id)"
                      />
                    </div>
                  </td>
                  <td
                    class="pe-0"
                  >
                    <VBtn
                      icon
                      variant="text"
                      color="default"
                      size="x-small"
                      class="me-2"
                      @click="viewLead(lead.id)"
                    >
                      <VIcon
                        :size="22"
                        icon="tabler-eye"
                      />
                    </VBtn>

                    <VBtn
                      icon
                      variant="text"
                      color="default"
                      size="x-small"
                      @click="updateLead(lead)"
                    >
                      <VIcon
                        :size="22"
                        icon="tabler-pencil"
                      />
                    </VBtn>
                  </td>

                  <td>
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
                    <span class="text-base">{{ lead.country }}</span>
                  </td>

                  <td>
                    -
                  </td>

                  <td>
                    <span class="text-capitalize text-base">{{ lead.agent }}</span>
                  </td>

                  <td>
                    <span class="text-capitalize text-base">{{ lead.boAgent }}</span>
                  </td>

                  <td>
                    <span class="text-capitalize text-base">{{ lead.product }}</span>
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

                  <td>
                    <span class="text-base">{{ lead.created_at }}</span>
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
          </VCardText>

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
      <CustomerDetails :lead="lead" @close:dialog="isDialogVisible = false"/>
    </VDialog>

    <VDialog
      v-model="isDialogVisible2"
      fullscreen
      :scrim="false"
      transition="dialog-bottom-transition"
    >
      <LeadDetails
        :lead="lead"
        @close:dialog="isDialogVisible2 = false"
      />
    </VDialog>

    <VDialog
      v-model="assignAgentDialogVisible"
      scrollable
      max-width="350"
    >
      <DialogCloseBtn @click="assignAgentDialogVisible = !assignAgentDialogVisible" />
      <VCard>
        <VCardItem class="pb-5">
          <VCardTitle>Select an Agent</VCardTitle>
        </VCardItem>

        <VDivider />

        <VCardText style="height: 200px;">
          <VRadioGroup v-model="selectedAgent">
            <VRadio
              v-for="agent in agents" 
              :label="agent.name"
              :value="agent.id"
              :key="agent.id"
            />
          </VRadioGroup>
        </VCardText>
        <VDivider />
        <VCardText class="d-flex justify-end flex-wrap gap-3 pt-5">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="assignAgentDialogVisible = false"
          >
            Close
          </VBtn>
          <VBtn
            :disabled="selectedAgent == null || selectedAgentLoading == true"
            :loading="selectedAgentLoading"
            @click="assignAgents"
          >
            Confirm
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <VDialog
      v-model="removeAgentDialogVisible"
      persistent
      class="v-dialog-sm"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="removeAgentDialogVisible = !removeAgentDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Remove Agents?">
        <VCardText>
          Remove assigned agents from the selected leads?
        </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="removeAgentDialogVisible = false"
          >
            Close
          </VBtn>
          <VBtn
            @click="removeAgents"
            :disabled="selectedAgentLoading"
            :loading="selectedAgentLoading"
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
