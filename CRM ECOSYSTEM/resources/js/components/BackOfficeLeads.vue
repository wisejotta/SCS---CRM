<script setup>

import LeadDetails from '@/components/LeadDetails.vue';

const snackbar = ref({
  active: false,
  text: null,
  color: null,
})

const rowPerPage = ref(10)
const leads = ref([])
const hover = ref({})

const currentPage = ref(1)
const totalPage = ref(1)
const totalLeads = ref(0)
const searchQuery = ref('')

const loading = ref(true)

const lead = ref(null)
const isDialogVisible = ref(false)

const viewLead = (id) => {
  lead.value = {id}
  isDialogVisible.value = true
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
  axios.post('/api/leads/back-office', {
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

const resolveUserRoleVariant = () => {
  const colors = ['warning', 'success', 'primary', 'info', 'admin', 'secondary'];
  const rndInt = Math.floor(Math.random() * (colors.length - 1))
  return colors[rndInt]
}

const avatarText = value => {
  if (!value)
    return ''
  const nameArray = value.split(' ')
  return nameArray.map(word => word.charAt(0).toUpperCase()).join('')
}
</script>

<template>
  <div>
    <VRow>
      <VCol cols="8">
        <VCard title="Leads">

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
                  PROGRESS
                </th>
                <th scope="col">
                  AGENT
                </th>
                <th scope="col">
                  PAID AMOUNT
                </th>
                <th scope="col">
                  COUNTRY
                </th>
                <th scope="col">
                  CREATED AT
                </th>
              </tr>
            </thead>
            <!-- 👉 table body -->
            <tbody>
              <tr
                v-for="lead in leads"
                :key="lead.id"
                style="height: 3.75rem;"
                @mouseover="hover = lead"
                @mouseleave="hover = {}"
                @click="viewLead(lead.id)"
                class="lead"
              >
                <td>
                  <div class="d-flex align-center">
                    <VAvatar
                      variant="tonal"
                      :color="resolveUserRoleVariant()"
                      class="me-3"
                      size="38"
                    >
                      <span>{{ avatarText(lead.name) }}</span>
                    </VAvatar>

                    <div class="d-flex flex-column">
                      <h6 class="text-base">
                        <div
                          class="font-weight-medium user-list-name"
                        >
                          {{ lead.name }}
                        </div>
                      </h6>
                      <span class="text-sm text-disabled">{{ lead.id }}</span>
                    </div>
                  </div>
                </td>

                <td>
                  <VProgressCircular
                    :rotate="360"
                    :model-value="lead.progress"
                    color="primary"
                  >
                  {{ lead.progress }}
                  </VProgressCircular>
                </td>

                <td>
                  <span class="text-base">{{ lead.agent }}</span>
                </td>

                <td>
                  <span class="text-base">{{ lead.paid }}</span>
                </td>

                <td>
                  <span class="text-base">{{ lead.country }}</span>
                </td>

                <td>
                  <span class="text-base">{{ lead.created_at }}</span>
                </td>
              </tr>
            </tbody>

            <!-- 👉 table footer  -->
            <tfoot v-show="!leads.length">
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

      <VCol cols="4">
        <VCard title="Details">
          <VCardText>
            <VTable>
              <tbody>
                <tr>
                  <td class="text-no-wrap">
                    <h4>Name</h4>
                  </td>
                  <td
                    class="text-center"
                  >
                    {{ hover.name }}
                  </td>
                </tr>
                <tr>
                  <td class="text-no-wrap">
                    <h4>Product</h4>
                  </td>
                  <td
                    class="text-center"
                  >
                    {{ hover.product }}
                  </td>
                </tr>
                <tr>
                  <td class="text-no-wrap">
                    <h4>Agent</h4>
                  </td>
                  <td
                    class="text-center"
                  >
                    {{ hover.agent }}
                  </td>
                </tr>
                <tr>
                  <td class="text-no-wrap">
                    <h4>Country</h4>
                  </td>
                  <td
                    class="text-center"
                  >
                    {{ hover.country }}
                  </td>
                </tr>
                <tr>
                  <td class="text-no-wrap">
                    <h4>Residence</h4>
                  </td>
                  <td
                    class="text-center"
                  >
                    {{ hover.residence }}
                  </td>
                </tr>
                <tr>
                  <td class="text-no-wrap">
                    <h4>Product</h4>
                  </td>
                  <td
                    class="text-center"
                  >
                    {{ hover.product }}
                  </td>
                </tr>
                <tr>
                  <td class="text-no-wrap">
                    <h4>Phone number</h4>
                  </td>
                  <td
                    class="text-center"
                  >
                    {{ hover.phone_number }}
                  </td>
                </tr>
                <tr>
                  <td class="text-no-wrap">
                    <h4>Office</h4>
                  </td>
                  <td
                    class="text-center"
                  >
                    {{ hover.office }}
                  </td>
                </tr>
                <tr>
                  <td class="text-no-wrap">
                    <h4>email</h4>
                  </td>
                  <td
                    class="text-center"
                  >
                    {{ hover.email }}
                  </td>
                </tr>
                <tr>
                  <td class="text-no-wrap">
                    <h4>Stage</h4>
                  </td>
                  <td
                    class="text-center"
                  >
                    <VChip
                      color="primary"
                      variant="elevated"
                      v-show="hover.status"
                    >
                      {{ hover.status }}
                    </VChip>
                  </td>
                </tr>
                <tr>
                  <td class="text-no-wrap">
                    <h4>Gender</h4>
                  </td>
                  <td
                    class="text-center"
                  >
                    {{ hover.gender }}
                  </td>
                </tr>
                <tr>
                  <td class="text-no-wrap">
                    <h4>DOB</h4>
                  </td>
                  <td
                    class="text-center"
                  >
                    {{ hover.dob }}
                  </td>
                </tr>
              </tbody>
            </VTable>
            
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
        :backoffice="true"
        @close:dialog="isDialogVisible = false"
        @fetch:leads="fetchLeads"
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

<style scoped>
.lead:hover {
  background-color: #eee;
  cursor: pointer;
}
</style>

<style lang="scss">
  @use "@core-scss/template/pages/misc.scss";
</style>
