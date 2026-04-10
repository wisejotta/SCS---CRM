<script setup>
import { emailValidator, requiredValidator } from '@validators';

const snackbar = ref({
  active: false,
  text: null,
  color: null,
})

const emit = defineEmits([
  'close:dialog',
  'refresh:agents',
])

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
const loading = ref(false)
const loading2 = ref(false)

const agent = ref(null)
const resetAgent = () => {
  agent.value = {
    firstname: null,
    lastname: null,
    email: null,
    password: null,
    type: 7,
    role: 3,
  }
}

resetAgent()

const isValid = ref(false)
const submit = () => {
  if(isValid.value) {
    loading2.value = true
    axios
      .put(`/api/agents`, agent.value)
      .then((r) => {
        snackbar.value.active = true
        snackbar.value.text = 'Agent added!'
        snackbar.value.color = null
        resetAgent()
        emit('refresh:agents')
      })
      .catch(e => {
        snackbar.value.active = true
        snackbar.value.text = 'Error adding agent!'
        snackbar.value.color = 'error'
        console.log(e)
      })
      .then(() => loading2.value = false)
  }
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

        <VToolbarTitle>Add New Agent</VToolbarTitle>
      </VToolbar>

      <VProgressLinear
        :active="loading"
        :indeterminate="loading"
        absolute
        bottom
        color="deep-purple accent-4"
      />

      <VRow class="mt-4 ml-4" v-if="!loading">
        <VCol cols="12" md="8">
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
                    <VTextField
                      v-model="agent.password"
                      label="Password"
                      placeholder="Password"
                      :type="isPasswordVisible ? 'text' : 'password'"
                      :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                      @click:append-inner="isPasswordVisible = !isPasswordVisible"
                      :rules="[requiredValidator]"
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
                    class="d-flex gap-4"
                  >
                    <VBtn type="submit"
                      :disabled="loading2 || !isValid"
                      :loading="loading2"
                      @click="submit">
                      Save
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