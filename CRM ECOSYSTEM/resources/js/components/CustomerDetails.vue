<script setup>
import { emailValidator, requiredValidator } from '@validators';

const snackbar = ref({
  active: false,
  text: null,
  color: null,
})

const emit = defineEmits([
  'close:dialog',
  'fetch:leads',
])

const props = defineProps({
  lead: {
    type: Object,
    required: false,
  },
  type: {
    type: Object,
    required: false,
  },
})

const genders = [
  {text: 'Male', value: 'male'},
  {text: 'Female', value: 'female'},
]

const statuses = [
  { value: 5, text: 'FILE_OPENING' },
  { value: 2, text: 'UPGRADE' },
]

const visas = [
  { value: 10, text: 'Single File Opening' },
  { value: 11, text: 'Family File Opening' },
  { value: 12, text: 'File Opening Gold 350' },
  { value: 13, text: 'File Opening Gold 500' },
  { value: 1, text: 'Student Visa' },
  { value: 2, text: 'Express Entry' },
  { value: 4, text: 'Tourist Visa' },
  { value: 14, text: 'Work Visa' },
]

const visa = ref(10)
const loading = ref(false)
const loading2 = ref(false)
const uploadLoading = ref(false)
const resultName = ref('')
const updateVisaLoading = ref(false)
const lead = ref(props.lead && props.lead.id ? props.lead : {})

lead.value.status = !props.type || props.type != 'upgrade' ? 5 : 2

const isValid = ref(lead.value.id ? true : false)

let products = ref([])
let agents = ref([])

if(lead.value.id) {
  loading.value = true
  axios
    .get(`/api/admin/customers/${lead.value.id}`)
    .then(r => {
      lead.value = r.data.lead
      if(lead.value.retainer) {
        visa.value = lead.value.retainer.visa_id
      }
    })
    .catch(e => console.log(e))
    .then(() => loading.value = false)
}

axios
  .get(`/api/admin/customers/setup`)
  .then((r) => {
    products.value = r.data.products
    agents.value = r.data.agents
  })

const submit = () => {
  if(isValid.value) {
    loading2.value = true
    const url = lead.value.id
      ? `/api/admin/customers/${lead.value.id}`
      : `/api/leads`
    axios
      .post(url, lead.value)
      .then((r) => {
        snackbar.value.text = 'Lead updated!'
        if(!lead.value.id) {
          snackbar.value.text = 'Lead added!'
        }
        snackbar.value.active = true
        snackbar.value.color = null
        if(!lead.value.id)
          lead.value = r.data.lead

        if(r.data && r.data.retainer) {
          lead.value.retainer.retainer = r.data.retainer
        }
        
        emit('fetch:leads')
      })
      .catch(e => {
        snackbar.value.active = true
        snackbar.value.text = 'Error updating lead!'
        snackbar.value.color = 'error'
        console.log(e)
      })
      .then(() => loading2.value = false)
  }
}

const uploadFile = () => document.getElementById('files2').click()
const uploadDoc = e => {
  const files = e.target.files || e.dataTransfer.files
  if(files.length) {
    uploadLoading.value = true
    const formData = new FormData();
    formData.append('file', files[0]);
    formData.append('name', resultName.value);
    const headers = { 'Content-Type': 'multipart/form-data' }
    axios
      .post(`/api/retainers/${lead.value.id}/results`, formData, { headers })
      .then((res) => {
        resultName.value = '';
        lead.value.results.push(res.data)
        snackbar.value.active = true
        snackbar.value.text = 'Results uploaded!'
        snackbar.value.color = null
      })
      .catch((e) => {
        snackbar.value.active = true
        snackbar.value.text = 'Could not upload file!'
        snackbar.value.color = 'error'
        console.log(e)
      })
      .then(() => uploadLoading.value = false)
  }
}

const updateVisa = () => {
  updateVisaLoading.value = true
  axios
    .get(`/api/retainers/${lead.value.id}/visa/${visa.value}`)
    .then((r) => {
      lead.value.retainers = r.data
      snackbar.value.active = true
      snackbar.value.text = 'Retainer updated!'
      snackbar.value.color = null
    }).catch(e => {
      snackbar.value.active = true
      snackbar.value.text = 'Could not update retainer!'
      snackbar.value.color = 'error'
      console.log(e)
    })
    .then(() => updateVisaLoading.value = false)
}

const removeResult = (result) => {
  result.loading = true
  axios.post(`/api/retainers/${lead.value.id}/results/delete`, {
    file: result.file
  }).then((res) => {
    lead.value.results = res.data
  })
  .catch((e) => {
    result.loading = false
    snackbar.value.active = true
    snackbar.value.text = 'Could not delete result!'
    snackbar.value.color = 'error'
    console.log(e)
  })
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

        <VToolbarTitle>{{ lead && lead.id ? `${lead.firstname || '' } ${lead.lastname || 'Loading'} - ${lead.id}` : 'Add new lead' }}</VToolbarTitle>
      </VToolbar>

      <VProgressLinear
        :active="loading"
        :indeterminate="loading"
        absolute
        bottom
        color="deep-purple accent-4"
      />

      <VRow class="mt-4 ml-4 mr-4" v-if="!loading">
        <VCol cols="12" md="8">
          <VCard title="DETAILS">
            <VDivider />
            <VCardText>
              <VForm v-model="isValid" @submit.prevent="() => {}">
                <VRow>
                  <VCol
                    cols="12"
                    md="6"
                  >
                    <VTextField
                      v-model="lead.firstname"
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
                      v-model="lead.lastname"
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
                      v-model="lead.email"
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
                      v-model="lead.phone_number"
                      label="Phone number"
                      placeholder="Phone number"
                      :rules="[requiredValidator]"
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <VTextField
                      v-model="lead.dob"
                      label="Date of birth"
                      placeholder="DD/MM/YYYY"
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <VTextField
                      v-model="lead.office_number"
                      label="Office number"
                      placeholder="Office number"
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <VSelect
                      v-model="lead.product"
                      :items="products"
                      item-title="text"
                      item-value="value"
                      label="Product type"
                      clearable
                      clear-icon="tabler-x"
                    /> 
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <VSelect
                      v-model="lead.status"
                      :items="statuses"
                      item-title="text"
                      item-value="value"
                      label="Lead status"
                      clearable
                      clear-icon="tabler-x"
                    /> 
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <VSelect
                      v-model="lead.agent"
                      :items="agents"
                      item-title="text"
                      item-value="value"
                      label="Sales agent"
                      clearable
                      clear-icon="tabler-x"
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <VSelect
                      v-model="lead.gender"
                      :items="genders"
                      item-title="text"
                      item-value="value"
                      label="Gender"
                      clearable
                      clear-icon="tabler-x"
                    /> 
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <VTextField
                      v-model="lead.profession"
                      label="Profession"
                      placeholder="Profession"
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <VTextField
                      v-model="lead.country"
                      label="Country"
                      placeholder="Country"
                      :rules="[requiredValidator]"
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <VTextField
                      v-model="lead.residence"
                      label="Residencey"
                      placeholder="Residencey"
                      :rules="[requiredValidator]"
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
                      {{ lead && lead.id ? 'Update' : 'Create' }}
                    </VBtn>
                  </VCol>
                </VRow>
              </VForm>
            </VCardText>
          </VCard>
        </VCol>

        <VCol cols="12" md="4" v-if="lead.id">
          <VCard title="RESULTS">
            <VDivider />
            <VCardText class="pa-3">
              <input @change="uploadDoc" class="d-none" type="file" id="files2" accept=".pdf" ref="files2" />
              <div class="mb-4">
                <VTextField
                  v-model.trim="resultName"
                  label="Name"
                >
                  <template #append>
                    <VBtn
                      :disabled="uploadLoading || resultName == ''"
                      :loading="uploadLoading"
                      @click="uploadFile"
                    >
                      UPLOAD
                    </VBtn>
                  </template>
                </VTextField>
              </div>

              <VRow class="pb-3">
                <VCol
                  cols="12"
                  md="12"
                  class="pa-3 pb-0 pt-2"
                  v-if="lead.results"
                  v-for="(result, i) in lead.results"
                  :key="i"
                >
                  <div
                    style="border: #eee solid 1px; border-radius: 5px;"
                  >
                    <VRow>
                      <VCol class="mt-3 ms-3 mb-3">
                        {{result.name}}
                      </VCol>
                      <VCol class="text-right">
                        <VBtn variant="text">
                          <a :href="result.file" target="_blank">VIEW</a>
                        </VBtn>
                        <VBtn
                          icon
                          variant="text"
                          color="default"
                          class="me-2"
                          :loading="result.loading"
                          :disabled="result.loading"
                          @click="removeResult(result)"
                        >
                          <VIcon
                            :size="22"
                            icon="tabler-trash"
                          />
                        </VBtn>
                      </VCol>
                    </VRow>
                  </div>
                </VCol>
              </VRow>
            </VCardText>
          </VCard>

          <VCard title="RETAINERS" class="mt-4">
            <VDivider />
            <VCardText>
              <VRow>
                <VCol
                  cols="12"
                  md="12"
                  class="pa-0 pb-2"
                  v-if="!lead.retainer || !lead.retainer.signed_at"
                >
                  <VSelect
                    v-model="visa"
                    :items="visas"
                    item-title="text"
                    item-value="value"
                    label="Visa type"
                  >
                    <template #append>
                      <VBtn
                        @click="updateVisa"
                        :disabled="updateVisaLoading"
                        :loading="updateVisaLoading"
                      >
                        ADD
                      </VBtn>
                    </template>
                  </VSelect>
                </VCol>

                <VCol
                  cols="12"
                  md="12"
                  class="pa-0 pt-2"
                  v-if="lead.retainers"
                  v-for="(retainer, i) in lead.retainers"
                  :key="i"
                >
                  <div
                    style="border: #eee solid 1px; border-radius: 5px;"
                    class="pa-3"
                  >
                    <VRow>
                      <VCol>
                        <span
                          class="text-sm text-disabled"
                        >
                          {{ retainer.name }}
                        </span>

                        <div class="mt-2">
                          Signed at:
                          <span class="text-sm text-disabled">
                            <i>{{ retainer.signed_at || 'N/A' }}</i>
                          </span>
                        </div>
                      </VCol>
                      <VCol class="text-right">
                        <VBtn variant="text">
                          <a :href="retainer.retainer" target="_blank">VIEW</a>
                        </VBtn>
                      </VCol>
                    </VRow>
                  </div>
                </VCol>
              </VRow>
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

<style>
.v-input__append {
  padding-top: 0!important
}
</style>