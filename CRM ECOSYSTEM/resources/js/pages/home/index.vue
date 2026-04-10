<script setup>

import CardStatisticsSalesOverview from '@/components/CardStatisticsSalesOverview.vue';
import EcommerceTotalProfitLineCharts from '@/components/EcommerceTotalProfitLineCharts.vue';
import LeadDetails from '@/components/LeadDetails.vue';
import BackOfficeLeads from '@/components/BackOfficeLeads.vue';
import { ref } from 'vue';

const user = JSON.parse(localStorage.getItem('user'))
const bo = user.type == 'BACK_OFFICE' || user.type == 'CUSTOMER_SERVICE'

let panels = [
  { slug: 'fresh-leads', name: 'Today\'s Fresh Leads' },
  { slug: 'reassigned', name: 'Reassigned' },
]

if(user.type == 'FILE_OPENING') {
  panels = panels.concat([
    { slug: 'collection', name: 'Collection' },
  ])
}

panels.push({ slug: 'upsales', name: 'Upsales' })

const rowPerPage = ref(10)
const searchQuery = ref('')
const leads = ref([])
const lead = ref(null)
const recallSwitch = ref(false)

const currentPage = ref(1)
const totalPage = ref(1)
const totalLeads = ref(0)

// 👉 Computing pagination data
const paginationData = computed(() => {
  const firstIndex = leads.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = leads.value.length + (currentPage.value - 1) * rowPerPage.value
  
  return `Showing ${ firstIndex } to ${ lastIndex } of ${ totalLeads.value } entries`
})

// 👉 watching current page
watchEffect(() => {
  if (currentPage.value > totalPage.value)
    currentPage.value = 1
})

const isDialogVisible = ref(false)

const openedPanel = ref(null)
const loading = ref(false)
const filter = ref({})

const chargebacks = ref([])
axios
  .get('/api/chargebacks/user')
  .then(r => chargebacks.value = r.data)
  .catch(e => console.log(e))

const commission = ref({})

axios
  .get(`/api/agents/commission`)
  .then(r => commission.value = r.data)
  .catch(e => console.log(e))
  .then(() => { })

let search = ''
const fetchLeads = () => {
  if(typeof openedPanel.value !== 'undefined' && openedPanel.value !== null) {
    loading.value = true
    filter.value = {
      perPage: rowPerPage.value,
      currentPage: currentPage.value,
      recallsOnly: recallSwitch.value,
      search,
    }
    axios.post(`/api/leads/${panels[openedPanel.value].slug}`, filter.value)
      .then((r) => {
        leads.value = r.data.leads
        totalPage.value = r.data.totalPage
        totalLeads.value = r.data.totalLeads
      })
      .catch(e => console.log(e))
      .then(() => loading.value = false)
  }
}
watchEffect(fetchLeads)

watch(openedPanel, () => leads.value = [])

let myTimeout
watch(searchQuery, () => {
  search = searchQuery.value
  openedPanel.value = null
  leads.value = []
  loading.value = true

  if(myTimeout)
    clearTimeout(myTimeout)

  myTimeout = setTimeout(() => {
    axios.post(`/api/leads/filter-section`, { search })
    .then((r) => {
      if(r.data) {
        openedPanel.value = r.data == 'fresh' ? 0 : 1
      } else {
        loading.value = false
      }
    })
    .catch(e => console.log(e))
  }, 500)

})

const router = useRouter()
const closeDialog = () => {
  isDialogVisible.value = false
  router.push('/home')
}

const viewLead = (l) => {
  lead.value = l
  isDialogVisible.value = true
  localStorage.setItem('lead', JSON.stringify(l))
  localStorage.setItem('lead-type', openedPanel.value)
  router.push({ path: 'home', query: { id: l.id }})
}

const route = useRoute()
if(route.query.id) {
  const sLead = JSON.parse(localStorage.getItem('lead'))
  const leadType = localStorage.getItem('lead-type')

  if(sLead && sLead.id == route.query.id && leadType) {
    lead.value = sLead
    openedPanel.value = leadType
    isDialogVisible.value = true
  }
}
</script>

<template>
  <div
    v-if="bo"
  >
  <BackOfficeLeads />
  </div>
  
  <div
   v-else
  >
    <VRow>
      <VCol cols="12" md="3">
        <CardStatisticsSalesOverview :commission="commission"/>
      </VCol>
      <VCol cols="12" md="3">
        <EcommerceTotalProfitLineCharts :commission="commission" />
      </VCol>

      <VCol cols="12" md="12" v-if="chargebacks.length > 0">
        <VCard title="Chargebacks">
          <VCardText>
            <!-- One -->
            <div
              class="demo-space-x pl-4 mt-2"
              style="background-color: red; border-radius: 5px; padding-bottom: 16px;"

              v-for="chargeback in chargebacks"
              :key="chargeback.id"
            >
              <h3 style="color: white;">
                -{{ chargeback.amount }}
              </h3>
              <VSpacer />
              <div style="background-color: #fff; padding: 5px 10px; border-radius: 100px;">
                {{ chargeback.created_at }}
              </div>
            </div>
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
            <div
              v-if="panel.slug == 'fresh-leads'"
              class="app-user-search-filter d-flex align-center flex-wrap gap-4 ma-4"
            >
              <div style="width: 15rem;">
                <VTextField
                  v-model="searchQuery"
                  placeholder="Search"
                  clearable
                  density="compact"
                />
              </div>
            </div>

            <VExpansionPanelTitle>{{ panel.name }}</VExpansionPanelTitle>

            <VProgressLinear
              v-if="panel.slug == 'fresh-leads'"
              :active="loading"
              :indeterminate="loading"
              absolute
              bottom
              color="deep-purple accent-4"
            />
            <VExpansionPanelText>

              <div
                v-if="panel.slug == 'fresh-leads'"
              >
                <VSwitch
                  v-model="recallSwitch"
                  label="Show re-calls only"
                />
                <VDivider />
              </div>

              <VTable class="text-no-wrap">
                <!-- 👉 table head -->
                <thead>
                  <tr>
                    <th scope="col">
                      NAME
                    </th>
                    <th scope="col">
                      FILE NUMBER
                    </th>
                    <th scope="col">
                      COUNTRY
                    </th>
                    <th scope="col">
                      ACTION
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
                      <div class="d-flex align-center">
                        <div class="d-flex flex-column">
                          <h6 class="text-base">
                            <RouterLink
                              :to="{ name: 'home' }"
                              class="font-weight-medium user-list-name"
                            >
                              {{ lead.firstname }} {{ lead.lastname }}
                            </RouterLink>
                          </h6>
                          <span class="text-sm text-disabled">{{ lead.phone_number }}</span>
                        </div>
                      </div>
                    </td>

                    <td>
                      <span class="text-capitalize text-base">{{ lead.id }}</span>
                    </td>

                    <td>
                      {{ lead.country }}
                    </td>

                    <td
                      class="text-center"
                      style="width: 5rem;"
                    >
                      <VBtn 
                        class="ml-2"
                        size="small"
                        @click="viewLead(lead)"
                      >
                        GO
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
      <LeadDetails
        :lead="lead"
        :filter="filter"
        :type="panels[openedPanel].slug"
        :reopen="true"
        @close:dialog="closeDialog"
        @fetch:leads="fetchLeads"
      />
    </VDialog>
  </div>
</template>
