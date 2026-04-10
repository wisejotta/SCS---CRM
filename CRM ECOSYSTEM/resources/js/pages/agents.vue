<script setup>

import AgentDetails from '@/components/AgentDetails.vue';
import UpdateAgent from '@/components/UpdateAgent.vue';
import CreateAgent from '@/components/CreateAgent.vue';

const snackbar = ref({
  active: false,
  text: null,
  color: null,
})

const rowPerPage = ref(10)
const agents = ref([])

const currentPage = ref(1)
const totalPage = ref(1)
const totalAgents = ref(0)

const loading = ref(true)
const loading2 = ref(false)

const paginationData = computed(() => {
  const firstIndex = agents.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = agents.value.length + (currentPage.value - 1) * rowPerPage.value
  return `Showing ${ firstIndex } to ${ lastIndex } of ${ totalAgents.value } entries`
})

watchEffect(() => {
  if (currentPage.value > totalPage.value)
    currentPage.value = 1
})

let interval = null
const fetchAgents = () => {
  loading.value = true
  axios.post('/api/agents', {
    perPage: rowPerPage.value,
    currentPage: currentPage.value,
  }).then((r) => {
    agents.value = r.data.agents
    totalPage.value = r.data.totalPage
    totalAgents.value = r.data.totalAgents

    if(!interval) clearInterval(interval)
    interval = setInterval(() => {
      agents.value.forEach((agent, index) => {
        if(agent.break && agent.break.seconds !== null) {
          agents.value[index].break.seconds += 1
          agents.value[index].break.time = convertStoMs(agent.break.seconds)
        }
      })
    }, 1000)
  })
  .catch(e => console.log(e))
  .then(() => loading.value = false)
}

const rmv = () => {
  loading2.value = true
  axios.delete(`/api/agents/${rmvAgent.value.id}`)
    .then((r) => {
      snackbar.value.active = true
      snackbar.value.text = 'Agent deleted!'
      snackbar.value.color = null

      isRemoveAgentDialogVisible.value = false
      fetchAgents()
    })
    .catch(e => {
      snackbar.value.active = true
      snackbar.value.text = 'Error updating agent!'
      snackbar.value.color = 'error'
      console.log(e)
    })
    .then(() => loading2.value = false)
}
watchEffect(fetchAgents)

const isDialogVisible = ref(false)
const isAgentInfoDialogVisible = ref(false)
const isCreateAgentDialogVisible = ref(false)
const isRemoveAgentDialogVisible = ref(false)
const agent = ref(null)
const updateAgent = (l) => {
  agent.value = l
  isDialogVisible.value = true
}

const agentInfo = (l) => {
  agent.value = l
  isAgentInfoDialogVisible.value = true
}

const rmvAgent = ref(null)
const rmvDialog = (a) => {
  rmvAgent.value = a
  isRemoveAgentDialogVisible.value = true
}

function convertStoMs(seconds) {
  let minutes = Math.floor(seconds / 60);
  let extraSeconds = seconds % 60;
  minutes = minutes < 10 ? "0" + minutes : minutes;
  extraSeconds = extraSeconds< 10 ? "0" + extraSeconds : extraSeconds;
  return `${minutes}:${extraSeconds}`
}

const getBreakTimberColor = (timer) => {
  let color = 'success'
  if(timer.type == 'cigarettes') {
    if(timer.seconds > 60 * 10) {
      color = 'error'
    } else if(timer.seconds > 60 * 8) {
      color = 'warning'
    }
  }

  if(timer.type == 'toilet') {
    if(timer.seconds > 60 * 5) {
      color = 'error'
    } else if(timer.seconds > 60 * 4) {
      color = 'warning'
    }
  }

  if(timer.type == 'lunch') {
    if(timer.seconds > 60 * 30) {
      color = 'error'
    } else if(timer.seconds > 60 * 25) {
      color = 'warning'
    }
  }
  return color
}
</script>

<template>
  <div>
    <VRow>
      <VCol cols="12">
        <VCard title="Agents">
          <VCardText class="d-flex flex-wrap py-4 gap-4">
            <VRow>
              <vCol cols="12" md="12" sm="12">
                <div class="demo-space-x">
                  <VBtn
                      color="primary"
                      size="small"
                      @click="isCreateAgentDialogVisible = true"
                    >
                      New Agent
                  </VBtn>
                </div>
              </vCol>

              <vCol cols="12" md="4" sm="12">
                <div  class="demo-space-x">
                  <div style="width: 80px;">
                    <VSelect
                        v-model="rowPerPage"
                        density="compact"
                        variant="outlined"
                        :items="[10, 20, 30, 50]"
                      />
                  </div>
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
                  ACTIONS
                </th>
                <th scope="col">
                  NAME
                </th>
                <th scope="col">
                  EMAIL
                </th>
                <th scope="col">
                  ROLE
                </th>
                <th scope="col">
                  TYPE
                </th>
                <th scope="col">
                  BREAK
                </th>
              </tr>
            </thead>
            <!-- 👉 table body -->
            <tbody>
              <tr
                v-for="agent in agents"
                :key="agent.id"
                style="height: 3.75rem;"
              >
                <!-- 👉 Actions -->
                <td
                  class="text-center"
                  style="width: 5rem;"
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
                        @click="agentInfo(agent)"
                      >
                        <VIcon
                          :size="22"
                          icon="tabler-eye"
                        />
                      </VBtn>
                    </template>

                    <p class="mb-0">
                      View Agent
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
                        @click="updateAgent(agent)"
                      >
                        <VIcon
                          :size="22"
                          icon="tabler-pencil"
                        />
                      </VBtn>
                    </template>

                    <p class="mb-0">
                      Edit Agent
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
                        @click="rmvDialog(agent)"
                      >
                        <VIcon
                          :size="22"
                          icon="tabler-trash"
                        />
                      </VBtn>
                    </template>

                    <p class="mb-0">
                      Delete Agent
                    </p>
                  </VTooltip>

                </td>
                <td>
                  <span class="text-base">{{ agent.name }}</span>
                </td>

                <td>
                  <span class="text-base">{{ agent.email }}</span>
                </td>

                <td>
                  <span class="text-base">{{ agent.role }}</span>
                </td>

                <td>
                  <span class="text-base">{{ agent.type }}</span>
                </td>

                <td>
                  <VChip
                    v-if="agent.break"
                    :color="getBreakTimberColor(agent.break)"
                  >
                    {{ agent.break.seconds !== null ? `${agent.break.type}: ${agent.break.time}` : agent.break.type }}
                  </VChip>

                  <VChip
                    v-else
                    color="success"
                  >
                    Available
                  </VChip>
                </td>
              </tr>
            </tbody>

            <!-- 👉 table footer  -->
            <tfoot v-show="!agents.length">
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
      <UpdateAgent
        :agent="agent"
        @close:dialog="isDialogVisible = false"
        @refresh:agents="fetchAgents"
      />
    </VDialog>

    <VDialog
      v-model="isAgentInfoDialogVisible"
      fullscreen
      :scrim="false"
      transition="dialog-bottom-transition"
    >
      <AgentDetails
        :agent="agent"
        @close:dialog="isAgentInfoDialogVisible = false"
      />
    </VDialog>

    <VDialog
      v-model="isCreateAgentDialogVisible"
      fullscreen
      :scrim="false"
      transition="dialog-bottom-transition"
    >
      <CreateAgent
        @close:dialog="isCreateAgentDialogVisible = false"
        @refresh:agents="fetchAgents"
      />
    </VDialog>

    <!-- Remove Agent -->
    <VDialog
        v-model="isRemoveAgentDialogVisible"
        persistent
        width="500"
      >
        <DialogCloseBtn @click="isRemoveAgentDialogVisible = !isRemoveAgentDialogVisible" />
        <VCard>
          <VCardText>
            Delete {{ rmvAgent.name }} from agents?
          </VCardText>
          <VCardText class="d-flex">
            <VBtn
                class="me-3"
                color="error"
                :loading="loading2"
                :disabled="loading2"
                @click="rmv"
              >
              Delete
              </VBtn>
              <VBtn
                color="secondary"
                variant="tonal"
                :disabled="loading2"
                @click="isRemoveAgentDialogVisible = !isRemoveAgentDialogVisible"
              >
                Cancel
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
