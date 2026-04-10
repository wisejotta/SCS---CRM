<script setup>
import { requiredValidator } from '@validators';

const snackbar = ref({
  active: false,
  text: null,
  color: null,
})

const chargebacks = ref([])
const currentPage = ref(1)
const totalPage = ref(1)

const loading = ref(true)
const addChargebackActive = ref(false)

const agents = ref([])
const selectedAgent = ref(null)
const amount = ref(null)
axios.get('/api/admin/agents').then((r) => agents.value = r.data)

const addCallback = () => {
  if(!selectedAgent.value) {
    snackbar.value.active = true
    snackbar.value.text = 'Agent required!'
    snackbar.value.color = 'warning'
    return
  } else if(!amount.value || amount.value == '') {
    snackbar.value.active = true
    snackbar.value.text = 'Amount required!'
    snackbar.value.color = 'warning'
    return
  }

  loading.value = true
  axios.post('/api/chargebacks', {
    agent: selectedAgent.value,
    amount: amount.value,
  })
  .then((r) => fetchChargebacks())
  .catch(e => console.log(e))
  .then(() => loading.value = false)
}

const fetchChargebacks = () => {
  loading.value = true
  addChargebackActive.value = false
  axios.get('/api/chargebacks')
  .then((r) => chargebacks.value = r.data)
  .catch(e => console.log(e))
  .then(() => loading.value = false)
}

fetchChargebacks()

</script>

<template>
  <div>
    <VRow>
      <VCol cols="12">
        <VCard title="Chargebacks">
          <VCardText class="d-flex flex-wrap py-4 gap-4">
            <VRow>
              <vCol cols="12" md="4" sm="12">
                <div  class="demo-space-x">
                  <VBtn
                      color="primary"
                      size="small"
                      @click="addChargebackActive = true"
                    >
                      New Chargeback
                  </VBtn>
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
                  AGENT NAME
                </th>
                <th scope="col">
                  AMOUNT
                </th>
                <th scope="col">
                  CREATED AT
                </th>
              </tr>
            </thead>
            <!-- 👉 table body -->
            <tbody>
              <tr
                v-for="chargeback in chargebacks"
                :key="chargeback.id"
                style="height: 3.75rem;"
              >
                <td>
                  <span class="text-base">{{ chargeback.agent }}</span>
                </td>

                <td>
                  <span class="text-base">{{ chargeback.amount }}</span>
                </td>

                <td>
                  <span class="text-base">{{ chargeback.created_at }}</span>
                </td>
              </tr>
            </tbody>

            <!-- 👉 table footer  -->
            <tfoot v-show="!chargebacks.length">
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
      v-model="addChargebackActive"
      persistent
      class="v-dialog-sm"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="addChargebackActive = !addChargebackActive" />

      <!-- Dialog Content -->
      <VCard title="Add chargeback">
        <VCardText>
          <VSelect
            v-model="selectedAgent"
            label="Agent"
            :items="agents"
            item-title="name"
            item-value="id"
            clearable
            clear-icon="tabler-x"
            class="mb-2"
            :rules="[requiredValidator]"
          />

          <VTextField
            v-model.trim="amount"
            placeholder="Amount"
            density="compact"
            prefix="$"
            :rules="[requiredValidator]"
          />
        </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="addChargebackActive = false"
          >
            Close
          </VBtn>
          <VBtn
            @click="addCallback"
            :disabled="loading"
            :loading="loading"
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
