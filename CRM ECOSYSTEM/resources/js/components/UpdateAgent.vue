<script setup>
import { emailValidator, requiredValidator } from '@validators';
import CardStatisticsSalesOverview from '@/components/CardStatisticsSalesOverview.vue';
import EcommerceTotalProfitLineCharts from '@/components/EcommerceTotalProfitLineCharts.vue';

const snackbar = ref({
  active: false,
  text: null,
  color: null,
})

const emit = defineEmits([
  'close:dialog',
  'refresh:agents',
])

const props = defineProps({
  agent: {
    type: Object,
    required: true,
  },
})

const statuses = [
  { value: 7, text: 'FILE_OPENING' },
  { value: 2, text: 'UPGRADE' },

  { value: 4, text: 'LEAD_ASSIGNER' },
  { value: 5, text: 'BACK_OFFICE' },
  { value: 6, text: 'CUSTOMER_SERVICE' },
]

const roles = [
  { value: 0, text: 'ADMIN' },
  { value: 3, text: 'AGENT' },
]

const isPasswordVisible = ref(true)
const loading = ref(true)
const loading2 = ref(false)
const loading3 = ref(false)
const loading4 = ref(false)
const agent = ref(props.agent)

const isValid = ref(false)
const isValid2 = ref(false)

const fetchAgent = () => {
  axios
    .get(`/api/agents/${props.agent.id}`)
    .then(r => agent.value = r.data)
    .catch(e => console.log(e))
    .then(() => loading.value = false)
}

fetchAgent()

const submit = () => {
  if(isValid.value) {
    loading2.value = true
    axios
      .post(`/api/agents/${props.agent.id}`, agent.value)
      .then(() => {
        snackbar.value.active = true
        snackbar.value.text = 'Agent updated!'
        snackbar.value.color = null
        emit('refresh:agents')
      })
      .catch(e => {
        snackbar.value.active = true
        snackbar.value.text = 'Error updating agent!'
        snackbar.value.color = 'error'
        console.log(e)
      })
      .then(() => loading2.value = false)
  }
}

const submit2 = () => {
  if(isValid2.value) {
    loading3.value = true
    axios
      .post(`/api/agents/goal/${props.agent.id}`, agent.value.targets)
      .then(() => {
        fetchAgent()
        snackbar.value.active = true
        snackbar.value.text = 'Goal updated!'
        snackbar.value.color = null
      })
      .catch(e => {
        snackbar.value.active = true
        snackbar.value.text = 'Error updating goal!'
        snackbar.value.color = 'error'
        console.log(e)
      })
      .then(() => loading3.value = false)
  }
}

const approvePassword = () => {
  loading4.value = true
  axios
    .put(`/api/admin/agents/reset-password/${props.agent.id}`)
    .then(() => {
      agent.value.passwordReset = false
      snackbar.value.active = true
      snackbar.value.text = 'Password approved!'
      snackbar.value.color = null
    })
    .catch(e => {
      snackbar.value.active = true
      snackbar.value.text = 'Error approving password!'
      snackbar.value.color = 'error'
      console.log(e)
    })
    .then(() => loading4.value = false)
}
</script>

<template>
  <VCard 
    style="background-image: url('/img/background3.png'); background-repeat: repeat;"
  >
    <div>
      <VToolbar color="primary">
        <VBtn
          icon
          variant="plain"
          @click="emit('close:dialog')"
        >
          <VIcon
            color="white"
            icon="tabler-x"
          />
        </VBtn>

        <VToolbarTitle>{{ `${agent.firstname} ${agent.lastname}` }}</VToolbarTitle>
      </VToolbar>

      <VProgressLinear
        :active="loading"
        :indeterminate="loading"
        absolute
        bottom
        color="deep-purple accent-4"
      />

      <VRow class="mt-4 ml-4 mr-4" v-if="!loading">
        <VCol cols="12" md="6">
          <VCard title="Details">
            <VDivider />
            <VCardText>
              <VForm v-model="isValid" @submit.prevent="() => {}">
                <VRow>
                  <VCol
                    cols="12"
                    md="6"
                  >
                    <VTextField
                      v-model="agent.firstname"
                      label="First Name"
                      placeholder="First Name"
                      :rules="[requiredValidator]"
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <VTextField
                      v-model="agent.lastname"
                      label="Last Name"
                      placeholder="Last Name"
                      :rules="[requiredValidator]"
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <VTextField
                      v-model="agent.email"
                      label="Email"
                      placeholder="Email"
                      :rules="[emailValidator, requiredValidator]"
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <VSelect
                      v-model="agent.type"
                      :items="statuses"
                      item-title="text"
                      item-value="value"
                      label="Agent type"
                    /> 
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <VSelect
                      v-model="agent.role"
                      :items="roles"
                      item-title="text"
                      item-value="value"
                      label="Role"
                    /> 
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <VTextField
                      v-model="agent.password"
                      label="Password"
                      placeholder="Password"
                      :type="isPasswordVisible ? 'text' : 'password'"
                      :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                      @click:append-inner="isPasswordVisible = !isPasswordVisible"
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    class="d-flex gap-4"
                  >
                    <VBtn type="submit"
                      :disabled="loading2"
                      :loading="loading2"
                      @click="submit">
                      Update
                    </VBtn>
                  </VCol>
                </VRow>
              </VForm>
            </VCardText>
          </VCard>
        </VCol>
        
        <VCol cols="12" md="3" v-if="agent.type == 2">
          <VCard title="Goals">
            <VDivider />
            <VCardText>
              <VForm v-model="isValid2" @submit.prevent="() => {}">
                <VRow>
                  <VCol
                    cols="12"
                    md="6"
                  >
                    Target
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <VTextField
                      v-model="agent.targets.goal"
                      prefix="$"
                      :rules="[requiredValidator]"
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    Percentage
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <VTextField
                      v-model="agent.targets.percentage"
                      suffix="%"
                      :rules="[requiredValidator]"
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    class="d-flex gap-4"
                  >
                    <VBtn type="submit"
                      :disabled="loading3"
                      :loading="loading3"
                      @click="submit2">
                      Update
                    </VBtn>
                  </VCol>
                </VRow>
              </VForm>
            </VCardText>
          </VCard>
        </VCol>
        
        <VCol cols="12" md="3">
          <CardStatisticsSalesOverview :commission="agent.commission" class="mb-2"/>
          <EcommerceTotalProfitLineCharts :commission="agent.commission" v-if="agent.type == 2"/>
          <VCard
            title="Reset password"
            class="mt-2"
          >
            <VBtn type="submit"
              class="mb-2 ms-4"
              :disabled="loading4 || !agent.passwordReset"
              :loading="loading4"
              @click="approvePassword">
              Approve password
            </VBtn>
          </VCard>
        </VCol>

        <VCol cols="12" md="3" v-if="agent.type != 2">
            <EcommerceTotalProfitLineCharts :commission="agent.commission"/>
        </VCol>

        <VCol cols="12" md="6" v-if="agent.type != 2">
          <VCard title="Goals">
            <VDivider />
            <VCardText>
              <VForm v-model="isValid2" @submit.prevent="() => {}">
                <VRow>
                  <VCol
                    cols="12"
                    md="9"
                  >
                    <h4>Target sales</h4>
                  </VCol>

                  <VCol
                    cols="12"
                    md="3"
                  >
                    <VTextField
                      v-model="agent.targets.goal"
                      :rules="[requiredValidator]"
                    />
                  </VCol>
                </VRow>
                <VDivider class="mt-2" />

                <VRow class="mt-1" v-for="product in agent.targets.products" :key="product.id">
                  <VCol
                    cols="12"
                    md="9"
                  >
                    {{ product.name }}
                  </VCol>
                  
                  <VCol
                    cols="12"
                    md="3"
                  >
                    <VTextField
                      v-model="product.commission"
                      prefix="R"
                      :rules="[requiredValidator]"
                    />
                  </VCol>
                </VRow>

                <VRow>
                  <VCol
                    cols="12"
                    class="d-flex gap-4"
                  >
                    <VBtn type="submit"
                      :disabled="loading3"
                      :loading="loading3"
                      @click="submit2">
                      Update
                    </VBtn>
                  </VCol>
                </VRow>
              </VForm>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>

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
  </VCard>

</template>