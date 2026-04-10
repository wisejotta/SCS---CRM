<script setup>

const props = defineProps({
  reassign: {
    type: Boolean,
    required: true,
  },
  type: {
    type: String,
    required: true,
  },
})

import CustomerDetails from '@/components/CustomerDetails.vue';
import LeadDetails from '@/components/LeadDetails.vue';

const snackbar = ref({
  active: false,
  text: null,
  color: null,
})

const assignAgentDialogVisible = ref(false)
const moveLeadsDialogVisible = ref(false)
const removeAgentDialogVisible = ref(false)
const removeLeadsDialogVisible = ref(false)
const uploadLeadsDialogVisible = ref(false)
const uploadLeadLoading = ref(false)
const selectAllLeads = ref(false)
const selectedRows = ref([])
const agents = ref([])
const selectedAgent = ref(null)
const selectedAgentLoading = ref(false)

const openedPanel = ref(null)
const panels = [
  { slug: 'english-fresh', name: 'English Fresh' },
  { slug: 'english-reassign', name: 'English Reassign' },
  { slug: 'english-collection', name: 'English Collection' },

  { slug: 'spanish-fresh', name: 'Spanish Fresh' },
  { slug: 'spanish-reassign', name: 'Spanish Reassign' },
  { slug: 'spanish-collection', name: 'Spanish Collection' },

  { slug: 'unassigned', name: 'Unassigned' },
]

axios.get('/api/admin/agents').then((r) => agents.value = r.data)

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

const moveTo = ref(null)

// 👉 watch if checkbox array is empty all checkbox should be uncheck
watch(selectedRows, () => {
  if (!selectedRows.value.length)
    selectAllLeads.value = false
}, { deep: true })

watch(openedPanel, () => {
    selectedRows.value = []
    leads.value = []
    if(openedPanel.value !== null && openedPanel.value >= 0) {
      const lan = panels[openedPanel.value].slug.split('-')[0]
      if(lan == 'english') {
        moveTo.value = 'spanish'
      } else if(lan == 'spanish') {
        moveTo.value = 'english'
      } else {
        moveTo.value = null
      }
    } else {
      moveTo.value = null
    }
})

const addRemoveIndividualCheckbox = checkID => {
  if (selectedRows.value.includes(checkID)) {
    const index = selectedRows.value.indexOf(checkID)
    selectedRows.value.splice(index, 1)
  } else {
    selectedRows.value.push(checkID)
    selectAllLeads.value = true
  }
}

const assignLeadsPopUp = () => {
  assignAgentDialogVisible.value = true
  selectedAgent.value = null
}

const moveLeadsPopUp = () => {
  moveLeadsDialogVisible.value = true
}

const assignAgents = () => {
  selectedAgentLoading.value = true
  axios.post(`/api/admin/agents/${selectedAgent.value}`, {
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

const removeLeads = () => {
  selectedAgentLoading.value = true
  axios.post('/api/leads/delete', { ids: selectedRows.value })
    .then((r) => {
      removeLeadsDialogVisible.value = false
      snackbar.value.active = true
      snackbar.value.text = 'Leads removed!'
      snackbar.value.color = null
      fetchLeads()
    })
    .catch(e => {
      snackbar.value.active = true
      snackbar.value.text = 'Error removing leads!'
      snackbar.value.color = 'error'
      console.log(e)
    })
    .then(() => selectedAgentLoading.value = false)
}

const moveLeads = () => {
  selectedAgentLoading.value = true
  axios.post('/api/leads/move', {
    ids: selectedRows.value,
    lan: moveTo.value
  })
    .then((r) => {
      moveLeadsDialogVisible.value = false
      snackbar.value.active = true
      snackbar.value.text = 'Leads moved!'
      snackbar.value.color = null
      fetchLeads()
    })
    .catch(e => {
      snackbar.value.active = true
      snackbar.value.text = 'Error moving leads!'
      snackbar.value.color = 'error'
      console.log(e)
    })
    .then(() => selectedAgentLoading.value = false)
}

const rowPerPage = ref(5)
const searchQuery = ref('')
const leads = ref([])

const currentPage = ref(1)
const totalPage = ref(1)
const totalLeads = ref(0)

const loading = ref(false)
const uploadCSVLoading = ref(false)

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
  if(typeof openedPanel.value !== 'undefined' && openedPanel.value !== null) {
    selectedRows.value = []
    loading.value = true
    axios.post(`/api/admin/leads/${panels[openedPanel.value].slug}`, {
      perPage: rowPerPage.value,
      currentPage: currentPage.value,
      q: search,
      selectedAgents: selectedAgents.value,
      reassign: props.reassign,
      type: props.type,
    }).then((r) => {
      leads.value = r.data.leads
      totalPage.value = r.data.totalPage
      totalLeads.value = r.data.totalLeads
    })
    .catch(e => console.log(e))
    .then(() => loading.value = false)
  }
}
watchEffect(fetchLeads)

let myTimeout
watch(searchQuery, async () => {
  search = searchQuery.value
  openedPanel.value = null
  loading.value = true

  if(myTimeout)
    clearTimeout(myTimeout)
  
  myTimeout = setTimeout(async () => {
    for(let i = 0; i < panels.length; i++) {
      let res = await  axios.post(`/api/admin/leads/${panels[i].slug}/1`, {
        perPage: rowPerPage.value,
        currentPage: currentPage.value,
        q: search,
        selectedAgents: selectedAgents.value,
        reassign: props.reassign,
        type: props.type,
      })

      if(res.data) {
        openedPanel.value = i
        fetchLeads
        break;
      } else if(i == panels.length - 1) {
        loading.value = false
      }
    }
  }, 500)
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

const headers = ref({})
const updateFields = ref(false)
const failedItems = ref(0)
const fields = ref([
  {
    field: 'firstname',
    label: 'Firstname',
  },
  {
    field: 'lastname',
    label: 'Lastname',
  },
  {
    field: 'email',
    label: 'Email*',
  },
  {
    field: 'country',
    label: 'Country',
  },
  {
    field: 'residence',
    label: 'Residence',
  },
  {
    field: 'phone_number',
    label: 'Phone number',
  },
  {
    field: 'office_number',
    label: 'Office number',
  },
  {
    field: 'gender',
    label: 'Gender',
  },
  {
    field: 'dob',
    label: 'DOB',
  },
  {
    field: 'profession',
    label: 'Profession',
  },
])

const uploadCSV = () => document.getElementById('files').click()
const getHeaders = e => {
  const files = e.target.files || e.dataTransfer.files
  if(files.length) {
    uploadCSVLoading.value = true
    const formData = new FormData();
    formData.append('csv', files[0]);
    const _headers = { 'Content-Type': 'multipart/form-data' }
    axios.post(`/api/admin/csv/headers`, formData, { _headers }).then((res) => {
      failedItems.value = 0
      headers.value = res.data;
      uploadLeadsDialogVisible.value = true;
    })
    .catch((e) => {
      snackbar.value.active = true
      snackbar.value.text = 'Error uploading CSV file!'
      snackbar.value.color = 'error'
      console.log(e)
    }).then(() => uploadCSVLoading.value = false)
  }
}

const uploadFile = () => {
  const hasEmail = fields.value.find(val => val.field == 'email' && val.value !== undefined)
  if(hasEmail) {
    uploadLeadLoading.value = true
    const data = { updateFields: updateFields.value }
    fields.value.forEach(val => {
      data[val.field] = val.value
    })
    
    axios.post(`/api/admin/csv/${headers.value.upload_id}`, data)
      .then((res) => {
        failedItems.value = res.data.failedItems
        if(!res.data.failedItems) {
          snackbar.value.active = true
          snackbar.value.text = 'CSV file uploaded!'
          snackbar.value.color = 'success'
          uploadLeadsDialogVisible.value = false
        }
        fetchLeads()
      })
      .catch((e) => {
        snackbar.value.active = true
        snackbar.value.text = 'Error uploading CSV file!'
        snackbar.value.color = 'error'
        console.log(e)
      })
      .then(() => uploadLeadLoading.value = false)
  } else {
    snackbar.value.active = true
    snackbar.value.text = 'Email field is required!'
    snackbar.value.color = 'warning'
  }
}

const downloadErrors = () => {
  window.open(`/api/admin/csv/${headers.value.upload_id}`, '_blank')
}
</script>

<template>
  <div>
    <VRow>
      <VCol cols="12">
        <VCard title="Filter Leads">
          <VCardText class="d-flex flex-wrap py-4 gap-4">
            <VRow>
              <vCol cols="12">
                <div class="demo-space-x">
                  <VBtn
                      color="primary"
                      size="small"
                      @click="updateLead"
                    >
                      New Lead
                  </VBtn>

                  <VBtn
                      color="primary"
                      @click="uploadCSV"
                      size="small"
                      :disabled="uploadCSVLoading"
                      :loading="uploadCSVLoading"
                    >
                      Upload csv
                  </VBtn>

                  <VBtn
                      color="primary"
                      @click="assignLeadsPopUp"
                      :disabled="selectedRows.length == 0"
                      size="small"
                    >
                      Assign
                  </VBtn>

                  <VBtn
                      color="primary"
                      @click="moveLeadsPopUp"
                      :disabled="selectedRows.length == 0 || !moveTo"
                      size="small"
                    >
                      Move{{ moveTo ? ` to ${moveTo}` : '' }}
                  </VBtn>

                  <VBtn
                      color="warning"
                      @click="removeAgentDialogVisible = true"
                      :disabled="selectedRows.length == 0"
                      size="small"
                    >
                      Unassign
                  </VBtn>

                  <VBtn
                      color="error"
                      @click="removeLeadsDialogVisible = true"
                      :disabled="selectedRows.length == 0"
                      size="small"
                    >
                      Remove Leads
                  </VBtn>
                </div>
              </vCol>

              <vCol cols="12" md="8" sm="12">
                <div  class="demo-space-x">
                  <div style="width: 80px;">
                    <VSelect
                        v-model="rowPerPage"
                        density="compact"
                        variant="outlined"
                        :items="[5, 10, 20, 30, 50]"
                      />
                    <input @change="getHeaders" class="d-none" type="file" id="files" accept=".csv" ref="files" />
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
              </vCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" md="12">
        <VExpansionPanels
          v-model="openedPanel"
        >
          <VExpansionPanel
            v-for="panel in panels"
            :key="panel.slug"
          >
            <VExpansionPanelTitle>{{ panel.name }}</VExpansionPanelTitle>
            <VProgressLinear
              v-if="panel.slug == 'english-fresh'"
              :active="loading"
              :indeterminate="loading"
              absolute
              bottom
              color="deep-purple accent-4"
            />
            <VExpansionPanelText>
              <VCardText class="d-flex flex-wrap py-4 gap-4"></VCardText>

              <VDivider />

              <VTable class="text-no-wrap">
                <!-- 👉 table head -->
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
                    <th scope="col">
                      ACTIONS
                    </th>
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
                      CS AGENT
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

                    <td
                      style="cursor: pointer;"
                      @click="addRemoveIndividualCheckbox(lead.id)"
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
                      <span class="text-base">{{ lead.country }}</span>
                    </td>

                    <td>
                      <span class="text-base">{{ lead.profession }}</span>
                    </td>

                    <td>
                      <span class="text-capitalize text-base">{{ lead.agent }}</span>
                    </td>

                    <td>
                      <span class="text-capitalize text-base">{{ lead.boAgent }}</span>
                    </td>

                    <td>
                      <span class="text-capitalize text-base">{{ lead.csAgent }}</span>
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
            </VExpansionPanelText>
          </VExpansionPanel>
        </VExpansionPanels>
      </VCol>
    </VRow>

    <VDialog
      v-model="isDialogVisible"
      fullscreen
      :scrim="false"
      transition="dialog-bottom-transition"
    >
      <CustomerDetails :lead="lead" @close:dialog="isDialogVisible = false" :type="props.type" @fetch:leads="fetchLeads"/>
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

    <VDialog
      v-model="removeLeadsDialogVisible"
      persistent
      class="v-dialog-sm"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="removeLeadsDialogVisible = !removeLeadsDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Remove Leads?">
        <VCardText>
          Delete selected Leads?
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
            @click="removeLeads"
            :disabled="selectedAgentLoading"
            :loading="selectedAgentLoading"
          >
            Confirm
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <VDialog
      v-model="moveLeadsDialogVisible"
      persistent
      class="v-dialog-sm"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="moveLeadsDialogVisible = !moveLeadsDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Move leads">
        <VCardText>
          Move Leads to {{ moveTo }}?
        </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="moveLeadsDialogVisible = false"
          >
            Close
          </VBtn>
          <VBtn
            @click="moveLeads"
            :disabled="selectedAgentLoading"
            :loading="selectedAgentLoading"
          >
            Confirm
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <VDialog
      v-model="isDialogVisible2"
      fullscreen
      :scrim="false"
      transition="dialog-bottom-transition"
    >
      <LeadDetails
        :lead="lead"
        :admin="true"
        @close:dialog="isDialogVisible2 = false"
        @fetch:leads="fetchLeads"
      />
    </VDialog>


    <VDialog
      v-model="uploadLeadsDialogVisible"
      persistent
      class="v-dialog-sm"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="uploadLeadsDialogVisible = !uploadLeadsDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Upload leads">
        <VCardText>
          <VTable>
            <tbody>
              <tr v-for="field in fields">
                <td
                  width="100"
                >
                  <h4>{{ field.label }}:</h4>
                </td>
                <td style="padding: 0;">
                  <VSelect
                    v-model="field.value"
                    :items="headers.headers"
                    :placeholder="field.label"
                    class="pt-2 mb-2"
                    item-title="field"
                    item-value="index"
                  /> 
                </td>
              </tr>
            </tbody>
          </VTable>

          <VDivider />

          <VCard
            class="mt-4"
            color="error"
            v-show="failedItems"
          >
            <div class="demo-space-x pl-4">
              <span>Failed items</span>
              <VChip
                color="primary"
                variant="elevated"
              >
                {{ failedItems }}
              </VChip>
              <VSpacer />
              <VBtn
                icon
                variant="text"
                color="default"
                class="me-2"
                @click="downloadErrors"
              >
                <VIcon
                  :size="22"
                  icon="tabler-download"
                />
              </VBtn>
            </div>
          </VCard>
        </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VCheckbox
            v-model="updateFields"
            label="Update existing"
          />
          <VSpacer />
          <VBtn
            color="secondary"
            variant="tonal"
            :disabled="uploadLeadLoading"
            @click="uploadLeadsDialogVisible = false"
          >
            Close
          </VBtn>
          <VBtn
            :disabled="uploadLeadLoading"
            :loading="uploadLeadLoading"
            @click="uploadFile"
          >
            Upload
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
