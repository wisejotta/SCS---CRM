<script setup>
import { VueSignaturePad } from 'vue-signature-pad';

const snackbar = ref({
  active: false,
  text: null,
  color: null,
})

const route = useRoute()
const loading = ref(true)
const retainer = ref(null)
const isDialogVisible = ref(false)
const signaturePad = ref(null)

const options = {
  penColor: "#000",
}

axios
  .get(`/api/retainers/${route.params.uuid}`)
  .then(r => retainer.value = r.data)
  .catch(e => console.log(e))
  .then(() => loading.value = false)

const sign = () => {
  const { isEmpty, data } = signaturePad.value.saveSignature()
  if(!isEmpty) {
    loading.value = true
    axios
      .post(`/api/retainers/${route.params.uuid}/sign`, { image: data })
      .then(r => {
        snackbar.value.active = true
        snackbar.value.text = 'Contract signed successfully.'
        snackbar.value.color = null
        setTimeout(() => location.reload(), 2000)
      })
      .catch(e => {
        snackbar.value.active = true
        snackbar.value.text = 'Error signing contract.'
        snackbar.value.color = 'error'
        console.log(e)
      })
      .catch(e => console.log(e))
      .then(() => loading.value = false)
  }
}

const clear = () => {
  signaturePad.value.clearSignature();
}

// edit total
// remove product and add date to leads table

</script>

<template>
  <div>
    <VProgressLinear
      :active="loading"
      :indeterminate="loading"
      absolute
      bottom
      color="deep-purple accent-4"
    />

    <div class="misc-wrapper" v-if="retainer">
      <iframe :src="retainer.file" frameborder="0" width="100%" height="1000px" style="margin-bottom: 70px;"></iframe>
      <div class="footer" v-if="!retainer.signed_at">
        <VBtn
          color="error"
          class="mb-4"
          @click="isDialogVisible = true"
        >
          Sign retainer agreement
        </VBtn>
      </div>
    </div>

    <VDialog
      v-model="isDialogVisible"
      persistent
      width="auto"
    >
      <VCard>
        <VCardTitle class="text-h5">
          Signature
        </VCardTitle>
        <VCardText>
          <VueSignaturePad
            width="464px"
            height="176px"
            ref="signaturePad"
            class="signature"
            :options="options"
          />
        </VCardText>
        <VCardActions>
          <VBtn
            color="error"
            @click="clear"
          >
            CLEAR
          </VBtn>
          <VSpacer></VSpacer>
          <VBtn
            color="green-darken-1"
            variant="text"
            :disabled="loading"
            @click="isDialogVisible = false"
          >
            BACK
          </VBtn>
          <VBtn
            color="green-darken-1"
            variant="text"
            :loading="loading"
            :disabled="loading"
            @click="sign"
          >
            SIGN DOCUMENT
          </VBtn>
        </VCardActions>
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

.footer {
  position: fixed;
  left: 0;
  bottom: 0;
  padding-top: 15px;
  width: 100%;
  background-color: white;
  text-align: center;
}

.signature {
  border: solid 2px #000;
  border-radius: 5px;
}
</style>

<route lang="yaml">
meta:
  layout: blank
</route>
