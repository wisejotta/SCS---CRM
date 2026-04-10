<script setup>
const snackbar = ref({
  active: false,
  text: null,
  color: null,
})

const loading = ref(false)
const password = ref('')
const show = ref(false)

const router = useRouter()
axios
  .get('/api/agents/reset-password')
  .then((r) => {
    if(!r.data) {
      router.push('/home')
    }
  })
  .catch(e => { console.log(e) })

const updatePassword = () => {
  loading.value = true
  axios.post('/api/agents/reset-password', {
    password: password.value
  }).then((r) => {
    snackbar.value.active = true
    snackbar.value.text = 'Password reset request sent!'
    snackbar.value.color = null
  })
  .catch(e => {
    snackbar.value.active = true
    snackbar.value.text = 'Error updating password!'
    snackbar.value.color = 'error'
    console.log(e)
  })
  .then(() => loading.value = false)
}
</script>

<template>
  <div class="misc-wrapper">
    <div class="text-center mb-12">
      <h4 class="text-h4 font-weight-medium mb-3">
        Update password
      </h4>

      <VTextField
        :append-inner-icon="show ? 'tabler-eye' : 'tabler-eye-off'"
        :type="show ? 'text' : 'password'"
        v-model="password"
        label="New password"
        @click:append-inner="show = !show"
      />

      <VBtn
        color="primary"
        class="mt-2"
        :loading="loading"
        :disabled="loading || password == ''"
        @click="updatePassword"
      >
        Update password
      </VBtn>
    </div>

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

<route lang="yaml">
meta:
  layout: blank
</route>
