<script setup>
import PaymentLinkTimeline from '@/components/PaymentLinkTimeline.vue';
import { emailValidator, requiredValidator } from '@validators';

const user = JSON.parse(localStorage.getItem('user'))
const cs = user.type == 'CUSTOMER_SERVICE'

const email = ref('')
const phone_number = ref('')

const snackbar = ref({
  active: false,
  text: null,
  color: null,
})

const holdVisible = ref(false)
const holdReason = ref('')
const holdLoading = ref(false)

const removeDocDialogVisible = ref(false)
const removeDocLoading = ref(false)
const isDialogVisible = ref(false)
const isDocDialogVisible = ref(false)
const isCallBackDialogVisible = ref(false)

const isCredsDialogVisible = ref(false)

const currentTab = ref(0)
const spouse = ref({})
const spouseLoading = ref(false)

const address = ref(null)
const amount = ref(null)
const backoffice = ref([])
const agents = ref([])
axios.get('/api/admin/agents').then((r) => agents.value = r.data)

const emit = defineEmits([
  'close:dialog',
  'fetch:leads',
])

const props = defineProps({
  lead: {
    type: Object,
    required: true,
  },
  type: {
    type: String,
    required: false,
  },
  reopen: {
    type: Boolean,
    required: false,
  },
  backoffice: {
    type: Boolean,
    required: false,
  },
  admin: {
    type: Boolean,
    required: false,
  },
})

const paymentMethods = [
  { value: 'authorize', text: 'Authorize.net'},
  { value: 'authorize-3d', text: 'Authorize.net 3D'},
  { value: 'square', text: 'Square'},
  { value: 'stripe-3d', text: 'Stripe 3D'},
  { value: 'stripe', text: 'Stripe'},
]

const languages = [
  { value: 'en', text: 'English' },
  { value: 'fr', text: 'French' },
  { value: 'es', text: 'Spanish' },
]

let products = []

let originalEmail = null

const loading = ref(true)
const uploadLoading = ref(false)
const linkLoading = ref(false)
const callBackLoading = ref(false)
const commentLoading = ref(false)
const lead = ref(props.lead)
const oldLead = ref(lead.value)
const isValid = ref(false)
const isValid2 = ref(false)
const form = ref({
  sendVia: null,
  method: null,
  lan: null,
  amount: null
})

const form2 = ref({
  sendVia: null,
  lan: null
})

const rules = {
  greaterAmount: value => (value <= lead.value.outstanding) || 'Amount greater than outstanding amount.',
}

const assignAgentDialogVisible = ref(false)
const selectedAgentLoading = ref(false)
const selectedAgent = ref(null)

const assignLeadsPopUp = () => {
  assignAgentDialogVisible.value = true
  selectedAgent.value = null
}

const assignAgents = () => {
  selectedAgentLoading.value = true
  axios.post(`/api/admin/agents/${selectedAgent.value}`, {
    ids: [lead.value.id]
  }).then((r) => {
    assignAgentDialogVisible.value = false
    snackbar.value.active = true
    snackbar.value.text = 'Agent assinged!'
    snackbar.value.color = null
    emit('fetch:leads')
  })
  .catch(e => {
    snackbar.value.active = true
    snackbar.value.text = 'Error assinging the agent!'
    snackbar.value.color = 'error'
    console.log(e)
  })
  .then(() => selectedAgentLoading.value = false)
}

const saveSpouse = () => {
  spouseLoading.value = true
  axios.post(`/api/leads/${lead.value.id}/spouse`, {
    spouse: spouse.value
  }).then((r) => {
    snackbar.value.active = true
    snackbar.value.text = 'Spouse saved!'
    snackbar.value.color = null
  })
  .catch(e => {
    snackbar.value.active = true
    snackbar.value.text = 'Error saving spouse!'
    snackbar.value.color = 'error'
    console.log(e)
  })
  .then(() => spouseLoading.value = false)
}

const paymentLink = () => {
  if(isValid.value) {
    form.value.email = email.value
    form.value.phone_number = phone_number.value
    form.value.office = lead.value.office
    form.value.product = product.value
    linkLoading.value = true
    
    axios
      .post(`/api/leads/${lead.value.id}/payment-link`, form.value)
      .then((r) => {
        isDialogVisible.value = false
        
        lead.value.email = originalEmail
        snackbar.value.active = true
        snackbar.value.text = 'Payment link sent to customer!'
        snackbar.value.color = null


        phone_number.value = lead.value.phone_number
        email.value = lead.value.email
      })
      .catch(e => {
        snackbar.value.active = true
        snackbar.value.text = 'Error creating payment link!'
        snackbar.value.color = 'error'
        console.log(e)
      })
      .then(() => linkLoading.value = false)
  }
}

const contractLink = () => {
  if(isValid2.value) {
    form2.value.email = lead.value.email
    linkLoading.value = true
    
    axios
      .post(`/api/retainers/${lead.value.id}/send`, form2.value)
      .then((r) => {
        setLead(r)
        isDocDialogVisible.value = false
      
        snackbar.value.active = true
        snackbar.value.text = 'Contract link sent to customer!'
        snackbar.value.color = null
      })
      .catch(e => {
        snackbar.value.active = true
        snackbar.value.text = 'Error sending contract link!'
        snackbar.value.color = 'error'
        console.log(e)
      })
      .then(() => linkLoading.value = false)
  }
}

const credsLoading = ref(false)
const creds = ref(null)
const sendCreds = () => {
  credsLoading.value = true
  axios
    .post(`/api/admin/customers/creds/${lead.value.id}/send`, {
      email: lead.value.email
    })
    .then((r) => {
      isCredsDialogVisible.value = false
    
      snackbar.value.active = true
      snackbar.value.text = 'Details sent to customer!'
      snackbar.value.color = null
    })
    .catch(e => {
      snackbar.value.active = true
      snackbar.value.text = 'Error sending details details!'
      snackbar.value.color = 'error'
      console.log(e)
    })
    .then(() => credsLoading.value = false)
}

const viewCreds = () => {
  if(!creds.value) {
    credsLoading.value = true
    axios
      .get(`/api/admin/customers/creds/${lead.value.id}/view`)
      .then((r) => {
        creds.value = r.data
      })
      .catch(e => {
        snackbar.value.active = true
        snackbar.value.text = 'Error getting details details!'
        snackbar.value.color = 'error'
        console.log(e)
      })
      .then(() => credsLoading.value = false)
  }
}

const setReason = (status, reason) => {
  holdLoading.value = true
  axios
    .post(`/api/leads/set-status/${lead.value.id}/${status}`, {reason})
    .then((r) => {
      holdVisible.value = false
      emit('fetch:leads')
      snackbar.value.active = true
      snackbar.value.text = 'Status updated'
      snackbar.value.color = null
    })
    .catch(e => {
      snackbar.value.active = true
      snackbar.value.text = 'Error updating status!'
      snackbar.value.color = 'error'
      console.log(e)
    })
    .then(() => holdLoading.value = false)
}

const date = ref('')
const callBack = () => {
  callBackLoading.value = true
  axios
    .post(`/api/leads/callbacks/${lead.value.id}`, {
      date: date.value
    })
    .then((r) => {
      isCallBackDialogVisible.value = false
      lead.value.callback.date = date.value
      
      snackbar.value.active = true
      snackbar.value.text = 'Callback set successfully!'
      snackbar.value.color = null
    })
    .catch(e => {
      snackbar.value.active = true
      snackbar.value.text = 'Error setting callback!'
      snackbar.value.color = 'error'
      console.log(e)
    })
    .then(() => callBackLoading.value = false)
}

const openCallBack = () => {
  date.value = lead.value.callback.date || ''
  isCallBackDialogVisible.value = true
}

const recallLoading = ref(false)
const recall = () => {
  recallLoading.value = true
  axios
    .get(`/api/leads/${lead.value.id}/recall`)
    .then((r) => {
      snackbar.value.active = true
      snackbar.value.text = 'Lead moved to Re-call'
      snackbar.value.color = 'success'
    })
    .catch(e => {
      snackbar.value.active = true
      snackbar.value.text = 'Error saving the comment!'
      snackbar.value.color = 'error'
      console.log(e)
    })
    .then(() => recallLoading.value = false)
}

const qAmount = ref('')
const product = ref(null)
const newComment = ref('')
const comments = ref([])
const documents = ref([])
const payments = ref([])
const panel = ref(null)

const makeComment = () => {
  commentLoading.value = true
  
  axios
    .post(`/api/leads/${lead.value.id}/comments`, {
      comment: newComment.value
    })
    .then((r) => {
      comments.value.unshift(r.data)
      newComment.value = ''
    })
    .catch(e => {
      snackbar.value.active = true
      snackbar.value.text = 'Error saving the comment!'
      snackbar.value.color = 'error'
      console.log(e)
    })
    .then(() => commentLoading.value = false)
}

let documentId = null;
const confirmDocRemovel = (id) => {
  removeDocDialogVisible.value = true
  documentId = id
}
const uploadFile = () => document.getElementById('files').click()
const uploadDoc = e => {
  const files = e.target.files || e.dataTransfer.files
  if(files.length) {
    uploadLoading.value = true
    const formData = new FormData();
    for(const file of files) {
      formData.append('files[]', file);
    }
    const headers = { 'Content-Type': 'multipart/form-data' }
    axios.post(`/api/leads/files/${lead.value.id}`, formData, { headers }).then((res) => {
      for(const doc of res.data) {
        documents.value.unshift(doc)
      }
    })
    .catch((e) => {
      snackbar.value.active = true
      snackbar.value.text = 'Error uploading the document!'
      snackbar.value.color = 'error'
      console.log(e)
    }).then(() => uploadLoading.value = false)
  }
}

const removeDocument = () => {
  removeDocLoading.value = true
  axios.delete(`/api/leads/files/${documentId}`).then((r) => {
    documents.value = documents.value.filter(val => val.id != documentId)
    removeDocDialogVisible.value = false
    snackbar.value.active = true
    snackbar.value.text = 'Document removed!'
    snackbar.value.color = null
  })
  .catch(e => {
    snackbar.value.active = true
    snackbar.value.text = 'Error removing docment!'
    snackbar.value.color = 'error'
    console.log(e)
  })
  .then(() => removeDocLoading.value = false)
}

axios
  .get(`/api/leads/${lead.value.id}/${props.admin ? 1 : 0}`)
  .then(r => setLead(r))
  .catch(e => console.log(e))
  .then(() => loading.value = false)

const nextOrPrev = (type = 'all') => {
  loading.value = true
  axios
    .get(`/api/leads/${type}/${lead.value.id}/${props.type}`)
    .then(r => setLead(r))
    .catch(e => console.log(e))
    .then(() => loading.value = false)
}

const router = useRouter()
function setLead(r) {
  setEditing()
  originalEmail = r.data.lead.email
  email.value = r.data.lead.email
  phone_number.value = r.data.lead.phone_number
  lead.value = r.data.lead
  oldLead.value = JSON.parse(JSON.stringify(r.data.lead)) // clone

  if(lead.value.spouse) {
    spouse.value = lead.value.spouse
  }

  product.value = r.data.lead.product
  qAmount.value = lead.value.amount_str
  comments.value = r.data.comments
  documents.value = r.data.documents
  payments.value = r.data.payments
  products = r.data.products
  backoffice.value = r.data.backoffice
  panel.value = payments.value.length ? 0 : null

  address.value = lead.value.residence || lead.value.country
  amount.value = parseInt(lead.value.amount)

  if(props.reopen) {
    localStorage.setItem('lead', JSON.stringify(r.data.lead))
    router.push({ path: 'home', query: { id: r.data.lead.id }})
  }
}

// EDIT LEAD
const editing = ref({
  field: null,
  value: null,
  loading: true,
})

const genders = [
  { text: 'Male', value: 'male' },
  { text: 'Female', value: 'female' },
]

const marital_statuses = [
  { text: 'Single', value: 'single' },
  { text: 'Married', value: 'married' },
  { text: 'Widowed', value: 'widowed' },
  { text: 'Divorced', value: 'divorced' },
]

const setEditing = (val = null) => {
  editing.value.field = val
  editing.value.value = lead.value[val]
  editing.value.loading = false
}

const toggleHold = () => {
  holdReason.value = ''
  holdVisible.value = !holdVisible.value
}

const updateField = () => {
  axios
    .put(`/api/leads/${lead.value.id}/${editing.value.field}`, {
      value: editing.value.value
    }).then(() => {
      if(editing.value.field == 'gender') {
        lead.value[editing.value.field] = genders.find(val => val.value == editing.value.value).text
      } else if(editing.value.field == 'marital_status') {
        lead.value[editing.value.field] = marital_statuses.find(val => val.value == editing.value.value).text
      } else {
        lead.value[editing.value.field] = editing.value.value
      }

      if(editing.value.field == 'email') {
        originalEmail = editing.value.value
      }
      setEditing()
      snackbar.value.active = true
      snackbar.value.text = 'Field updated!'
      snackbar.value.color = null
  })
  .catch(e => {
    editing.value.loading = false
    snackbar.value.active = true
    snackbar.value.text = 'Error updating field!'
    snackbar.value.color = 'error'
    console.log(e)
  })
}

const backOfficeDocColor = (status) => {
  if(status == 'pre-approved') return 'success'
  if(status == 'required') return 'error'
  if(status == 'optional') return 'primary'
  if(status == 'review') return 'info'
}

const setBackOfficeStatus = (bo, name, status) => {
  bo.status = status
  axios.get(`/api/leads/${lead.value.id}/back-office/${name}/${status}`)
    .then((r) => {
      emit('fetch:leads')
    })
}

const openDoc = (link) => {
  window.open(link, "_blank")
}

let slug = ''
const uploadFile2 = (_slug) => {
  slug = _slug
  document.getElementById('files2').click()
}

const uploadDoc2 = e => {
  const files = e.target.files || e.dataTransfer.files
  if(files.length) {
    backoffice.value[slug].loading = true

    const formData = new FormData();
    formData.append('file', files[0]);
    formData.append('name', files[0].name);

    const headers = { 'Content-Type': 'multipart/form-data' }
    axios
      .post(`/api/leads/${lead.value.id}/back-office/doc/${slug}`, formData, { headers })
      .then((res) => {
        backoffice.value[slug].doc = res.data
        if(backoffice.value[slug].status == 'required') {
          backoffice.value[slug].status = 'review'
        }
      })
      .catch((e) => { })
      .then(() => {
        backoffice.value[slug].loading = false
      })
  }
}

// END EDIT LEAD

const interval = setInterval(() => {
  let last = payments.value.length
    ? payments.value[0].id
    : 0

  axios
    .get(`/api/leads/${lead.value.id}/latest/${last}`)
    .then((r) => {
      r.data.forEach(element => {
        payments.value.unshift(element)
      })
      panel.value = r.data.length ? 0 : panel.value
    })
    .catch(e => console.log(e))
}, 5000)

const closeDialog = () => {
  holdReason.value = ''
  emit('close:dialog')
  clearInterval(interval)
}

const viewResult = (file) => window.open(file, '_blank')

watch(product, val => {
  const p = products.find(p => p.value == val)
  if(p) {
    qAmount.value = p.amount_str
    lead.value.outstanding_str = p.outstanding_str
    lead.value.outstanding = p.outstanding
  }
})
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
          @click="closeDialog"
        >
          <VIcon
            color="white"
            icon="tabler-x"
          />
        </VBtn>

        <VToolbarTitle>{{ `${lead.firstname} ${lead.lastname} - ${lead.id}` }}</VToolbarTitle>

        <VBtn
          icon="tabler-chevron-left"
          variant="text"
          color="white"
          @click="nextOrPrev('prev')"
        />

        <VBtn
          icon="tabler-chevron-right"
          variant="text"
          color="white"
          @click="nextOrPrev('next')"
        />
      </VToolbar>

      <VProgressLinear
        :active="loading"
        :indeterminate="loading"
        absolute
        bottom
        color="deep-purple accent-4"
      />
    
      <VRow 
        class="mt-4 ml-4 mr-4"
        v-if="!loading"
      >
        <VCol cols="12" md="8">
          <VCard>
            <template #append>
              <div class="mt-n4 me-n2">
                <VBtn
                  rounded="pill"
                  color="primary"
                  size="small"
                  v-if="admin"
                  @click="assignLeadsPopUp"
                >
                  Assign agent
                </VBtn>
                
                <VBtn
                  color="primary"
                  variant="text"
                  v-if="!props.backoffice"
                >
                  Options
                  <VIcon
                    end
                    icon="tabler-dots-vertical"
                  />

                  <VMenu activator="parent">
                    <VList>
                      <VListItem
                        @click="isDialogVisible = true"
                      >
                        <VListItemTitle>Payment Link</VListItemTitle>
                      </VListItem>

                      <VListItem
                        v-if="lead.retainer"
                        @click="isDocDialogVisible = true"
                      >
                        <VListItemTitle>Send Results</VListItemTitle>
                      </VListItem>

                      <VListItem
                        @click="isCredsDialogVisible = true"
                      >
                        <VListItemTitle>Dashboard Details</VListItemTitle>
                      </VListItem>
                    </VList>
                  </VMenu>
                </VBtn>
              </div>
            </template>
            <VDivider />

            <VCardText>
              <VTabs
                v-model="currentTab"
                class="v-tabs-pill"
              >
                <VTab>Details</VTab>
                <VTab v-if="lead.marital_status == 'Married'">Spouse</VTab>
              </VTabs>
            </VCardText>

            <VCardText>

            <VWindow v-model="currentTab">
              <VWindowItem>
                <VTable>
                  <tbody>
                    <tr>
                      <!-- Firstname -->
                      <td class="text-no-wrap" width="70">
                        <h4>Firstname:</h4>
                      </td>
                      <td
                        class="text-right"
                        v-if="editing.field != 'firstname'"
                      >
                        {{ lead.firstname }}
                      </td>
                      <td v-else style="padding: 0;">
                        <VTextField
                          v-model="editing.value"
                          type="text"
                          color="primary"
                          append-icon="tabler-x"
                          @click:append="setEditing"
                        />
                      </td>
                      <td class="text-right" width="70">
                        <VBtn
                          icon
                          variant="text"
                          color="default"
                          size="small"
                          class="text-high-emphasis ms-n1 edit"
                          :class="{ 'd-none': editing.field == 'firstname' || editing.loading }"
                          @click="setEditing('firstname')"
                        >
                          <VIcon
                            size="22"
                            icon="tabler-edit"
                          />
                        </VBtn>
                        <VBtn
                          color="primary"
                          size="small"
                          v-show="editing.field == 'firstname'"
                          :loading="editing.loading"
                          :disabled="editing.loading"
                          @click="updateField"
                        >
                          SAVE
                        </VBtn>
                      </td>

                      <!-- Lastname -->
                      <td class="text-no-wrap" width="70">
                        <h4>Lastname:</h4>
                      </td>
                      <td
                        class="text-right"
                        v-if="editing.field != 'lastname'"
                      >
                        {{ lead.lastname }}
                      </td>
                      <td v-else>
                        <VTextField
                          v-model="editing.value"
                          type="text"
                          color="primary"
                          append-icon="tabler-x"
                          @click:append="setEditing"
                        />
                      </td>
                      <td class="text-right" width="70">
                        <VBtn
                          icon
                          variant="text"
                          color="default"
                          size="small"
                          class="text-high-emphasis ms-n1 edit"
                          :class="{ 'd-none': editing.field == 'lastname' || editing.loading }"
                          @click="setEditing('lastname')"
                        >
                          <VIcon
                            size="22"
                            icon="tabler-edit"
                          />
                        </VBtn>
                        <VBtn
                          color="primary"
                          size="small"
                          v-show="editing.field == 'lastname'"
                          :loading="editing.loading"
                          :disabled="editing.loading"
                          @click="updateField"
                        >
                          SAVE
                        </VBtn>
                      </td>
                    </tr>

                    <tr>
                      <!-- Date of birth -->
                      <td class="text-no-wrap" width="70">
                        <h4>Date of birth:</h4>
                      </td>
                      <td
                        class="text-right"
                        v-if="editing.field != 'dob'"
                      >
                        {{ lead.dob }}
                      </td>
                      <td v-else style="padding: 0;">
                        <VTextField
                          v-model="editing.value"
                          type="text"
                          color="primary"
                          append-icon="tabler-x"
                          @click:append="setEditing"
                        />
                      </td>
                      <td class="text-right" width="70">
                        <VBtn
                          icon
                          variant="text"
                          color="default"
                          size="small"
                          class="text-high-emphasis ms-n1 edit"
                          :class="{ 'd-none': editing.field == 'dob' || editing.loading }"
                          @click="setEditing('dob')"
                        >
                          <VIcon
                            size="22"
                            icon="tabler-edit"
                          />
                        </VBtn>
                        <VBtn
                          color="primary"
                          size="small"
                          v-show="editing.field == 'dob'"
                          :loading="editing.loading"
                          :disabled="editing.loading"
                          @click="updateField"
                        >
                          SAVE
                        </VBtn>
                      </td>

                      <!-- Age -->
                      <td class="text-no-wrap" width="70">
                        <h4>Age:</h4>
                      </td>
                      <td
                        class="text-right"
                      >
                        {{ lead.age }}
                      </td>
                      <td class="text-right" width="70"></td>
                    </tr>

                    <tr>
                      <!-- Gender -->
                      <td class="text-no-wrap" width="70">
                        <h4>Gender:</h4>
                      </td>
                      <td
                        class="text-right"
                        v-if="editing.field != 'gender'"
                      >
                        {{ lead.gender }}
                      </td>
                      <td v-else style="padding: 0;">
                        <VSelect
                          v-model="editing.value"
                          :items="genders"
                          item-title="text"
                          item-value="value"
                        /> 
                      </td>
                      <td class="text-right" width="70">
                        <VBtn
                          icon
                          variant="text"
                          color="default"
                          size="small"
                          class="text-high-emphasis ms-n1 edit"
                          :class="{ 'd-none': editing.field == 'gender' || editing.loading }"
                          @click="setEditing('gender')"
                        >
                          <VIcon
                            size="22"
                            icon="tabler-edit"
                          />
                        </VBtn>
                        <VBtn
                          color="primary"
                          size="small"
                          v-show="editing.field == 'gender'"
                          :loading="editing.loading"
                          :disabled="editing.loading"
                          @click="updateField"
                        >
                          SAVE
                        </VBtn>
                      </td>

                      <!-- Marital status -->
                      <td class="text-no-wrap" width="70">
                        <h4>Marital status:</h4>
                      </td>
                      <td
                        class="text-right"
                        v-if="editing.field != 'marital_status'"
                      >
                        {{ lead.marital_status }}
                      </td>
                      <td v-else>
                        <VSelect
                          v-model="editing.value"
                          :items="marital_statuses"
                          item-title="text"
                          item-value="value"
                        /> 
                      </td>
                      <td class="text-right" width="70">
                        <VBtn
                          icon
                          variant="text"
                          color="default"
                          size="small"
                          class="text-high-emphasis ms-n1 edit"
                          :class="{ 'd-none': editing.field == 'marital_status' || editing.loading }"
                          @click="setEditing('marital_status')"
                        >
                          <VIcon
                            size="22"
                            icon="tabler-edit"
                          />
                        </VBtn>
                        <VBtn
                          color="primary"
                          size="small"
                          v-show="editing.field == 'marital_status'"
                          :loading="editing.loading"
                          :disabled="editing.loading"
                          @click="updateField"
                        >
                          SAVE
                        </VBtn>
                      </td>
                    </tr>

                    <tr>
                      <!-- phone_number -->
                      <td class="text-no-wrap" width="70">
                        <h4>Mobile number:</h4>
                      </td>
                      <td
                        class="text-right"
                        v-if="editing.field != 'phone_number'"
                      >
                        {{ lead.phone_number }}
                      </td>
                      <td v-else style="padding: 0;">
                        <VTextField
                          v-model="editing.value"
                          type="text"
                          color="primary"
                          append-icon="tabler-x"
                          @click:append="setEditing"
                        />
                      </td>
                      <td class="text-right" width="70">
                        <VBtn
                          icon
                          variant="text"
                          color="default"
                          size="small"
                          class="text-high-emphasis ms-n1 edit"
                          :class="{ 'd-none': editing.field == 'phone_number' || editing.loading }"
                          @click="setEditing('phone_number')"
                        >
                          <VIcon
                            size="22"
                            icon="tabler-edit"
                          />
                        </VBtn>
                        <VBtn
                          color="primary"
                          size="small"
                          v-show="editing.field == 'phone_number'"
                          :loading="editing.loading"
                          :disabled="editing.loading"
                          @click="updateField"
                        >
                          SAVE
                        </VBtn>
                      </td>

                      <!-- Office -->
                      <td class="text-no-wrap" width="70">
                        <h4>Office:</h4>
                      </td>
                      <td
                        class="text-right"
                        v-if="editing.field != 'office'"
                      >
                        {{ lead.office }}
                      </td>
                      <td v-else>
                        <VTextField
                          v-model="editing.value"
                          type="text"
                          color="primary"
                          append-icon="tabler-x"
                          @click:append="setEditing"
                        />
                      </td>
                      <td class="text-right" width="70">
                        <VBtn
                          icon
                          variant="text"
                          color="default"
                          size="small"
                          class="text-high-emphasis ms-n1 edit"
                          :class="{ 'd-none': editing.field == 'office' || editing.loading }"
                          @click="setEditing('office')"
                        >
                          <VIcon
                            size="22"
                            icon="tabler-edit"
                          />
                        </VBtn>
                        <VBtn
                          color="primary"
                          size="small"
                          v-show="editing.field == 'office'"
                          :loading="editing.loading"
                          :disabled="editing.loading"
                          @click="updateField"
                        >
                          SAVE
                        </VBtn>
                      </td>
                    </tr>
                  </tbody>
                </VTable>

                <VTable>
                  <tbody>
                    <tr>
                      <!-- Email -->
                      <td class="text-no-wrap" width="70">
                        <h4>Email:</h4>
                      </td>
                      <td
                        class="text-right"
                        v-if="editing.field != 'email'"
                      >
                        {{ lead.email }}
                      </td>
                      <td v-else>
                        <VTextField
                          v-model="editing.value"
                          type="text"
                          color="primary"
                          append-icon="tabler-x"
                          @click:append="setEditing"
                        />
                      </td>
                      <td class="text-right" width="70">
                        <VBtn
                          icon
                          variant="text"
                          color="default"
                          size="small"
                          class="text-high-emphasis ms-n1 edit"
                          :class="{ 'd-none': editing.field == 'email' || editing.loading }"
                          @click="setEditing('email')"
                        >
                          <VIcon
                            size="22"
                            icon="tabler-edit"
                          />
                        </VBtn>
                        <VBtn
                          color="primary"
                          size="small"
                          v-show="editing.field == 'email'"
                          :loading="editing.loading"
                          :disabled="editing.loading"
                          @click="updateField"
                        >
                          SAVE
                        </VBtn>
                      </td>
                    </tr>
                  </tbody>
                </VTable>

                <VTable>
                  <tbody>
                    <tr>
                      <!-- Country -->
                      <td class="text-no-wrap" width="70">
                        <h4>Country:</h4>
                      </td>
                      <td
                        class="text-right"
                        v-if="editing.field != 'country'"
                      >
                        {{ lead.country }}
                      </td>
                      <td v-else style="padding: 0;">
                        <VTextField
                          v-model="editing.value"
                          type="text"
                          color="primary"
                          append-icon="tabler-x"
                          @click:append="setEditing"
                        />
                      </td>
                      <td class="text-right" width="70">
                        <VBtn
                          icon
                          variant="text"
                          color="default"
                          size="small"
                          class="text-high-emphasis ms-n1 edit"
                          :class="{ 'd-none': editing.field == 'country' || editing.loading }"
                          @click="setEditing('country')"
                        >
                          <VIcon
                            size="22"
                            icon="tabler-edit"
                          />
                        </VBtn>
                        <VBtn
                          color="primary"
                          size="small"
                          v-show="editing.field == 'country'"
                          :loading="editing.loading"
                          :disabled="editing.loading"
                          @click="updateField"
                        >
                          SAVE
                        </VBtn>
                      </td>

                      <!-- Country of residence -->
                      <td class="text-no-wrap" width="70">
                        <h4>Country of residence:</h4>
                      </td>
                      <td
                        class="text-right"
                        v-if="editing.field != 'residence'"
                      >
                        {{ lead.residence }}
                      </td>
                      <td v-else>
                        <VTextField
                          v-model="editing.value"
                          type="text"
                          color="primary"
                          append-icon="tabler-x"
                          @click:append="setEditing"
                        />
                      </td>
                      <td class="text-right" width="70">
                        <VBtn
                          icon
                          variant="text"
                          color="default"
                          size="small"
                          class="text-high-emphasis ms-n1 edit"
                          :class="{ 'd-none': editing.field == 'residence' || editing.loading }"
                          @click="setEditing('residence')"
                        >
                          <VIcon
                            size="22"
                            icon="tabler-edit"
                          />
                        </VBtn>
                        <VBtn
                          color="primary"
                          size="small"
                          v-show="editing.field == 'residence'"
                          :loading="editing.loading"
                          :disabled="editing.loading"
                          @click="updateField"
                        >
                          SAVE
                        </VBtn>
                      </td>
                    </tr>

                    <tr>
                      <!-- Language -->
                      <td class="text-no-wrap" width="70">
                        <h4>Language:</h4>
                      </td>
                      <td
                        class="text-right"
                        v-if="editing.field != 'language'"
                      >
                        {{ lead.language || '-' }}
                      </td>
                      <td v-else style="padding: 0;">
                        <VTextField
                          v-model="editing.value"
                          type="text"
                          color="primary"
                          append-icon="tabler-x"
                          @click:append="setEditing"
                        />
                      </td>
                      <td class="text-right" width="70">
                        <VBtn
                          icon
                          variant="text"
                          color="default"
                          size="small"
                          class="text-high-emphasis ms-n1 edit"
                          :class="{ 'd-none': editing.field == 'language' || editing.loading }"
                          @click="setEditing('language')"
                        >
                          <VIcon
                            size="22"
                            icon="tabler-edit"
                          />
                        </VBtn>
                        <VBtn
                          color="primary"
                          size="small"
                          v-show="editing.field == 'language'"
                          :loading="editing.loading"
                          :disabled="editing.loading"
                          @click="updateField"
                        >
                          SAVE
                        </VBtn>
                      </td>

                      <!-- Education -->
                      <td class="text-no-wrap" width="70">
                        <h4>Education:</h4>
                      </td>
                      <td
                        class="text-right"
                        v-if="editing.field != 'education'"
                      >
                        {{ lead.education || '-' }}
                      </td>
                      <td v-else>
                        <VTextField
                          v-model="editing.value"
                          type="text"
                          color="primary"
                          append-icon="tabler-x"
                          @click:append="setEditing"
                        />
                      </td>
                      <td class="text-right" width="70">
                        <VBtn
                          icon
                          variant="text"
                          color="default"
                          size="small"
                          class="text-high-emphasis ms-n1 edit"
                          :class="{ 'd-none': editing.field == 'education' || editing.loading }"
                          @click="setEditing('education')"
                        >
                          <VIcon
                            size="22"
                            icon="tabler-edit"
                          />
                        </VBtn>
                        <VBtn
                          color="primary"
                          size="small"
                          v-show="editing.field == 'education'"
                          :loading="editing.loading"
                          :disabled="editing.loading"
                          @click="updateField"
                        >
                          SAVE
                        </VBtn>
                      </td>
                    </tr>

                    <tr>
                      <!-- Occupation -->
                      <td class="text-no-wrap" width="70">
                        <h4>Occupation:</h4>
                      </td>
                      <td
                        class="text-right"
                        v-if="editing.field != 'occupation'"
                      >
                        {{ lead.occupation || '-' }}
                      </td>
                      <td v-else style="padding: 0;">
                        <VTextField
                          v-model="editing.value"
                          type="text"
                          color="primary"
                          append-icon="tabler-x"
                          @click:append="setEditing"
                        />
                      </td>
                      <td class="text-right" width="70">
                        <VBtn
                          icon
                          variant="text"
                          color="default"
                          size="small"
                          class="text-high-emphasis ms-n1 edit"
                          :class="{ 'd-none': editing.field == 'occupation' || editing.loading }"
                          @click="setEditing('occupation')"
                        >
                          <VIcon
                            size="22"
                            icon="tabler-edit"
                          />
                        </VBtn>
                        <VBtn
                          color="primary"
                          size="small"
                          v-show="editing.field == 'occupation'"
                          :loading="editing.loading"
                          :disabled="editing.loading"
                          @click="updateField"
                        >
                          SAVE
                        </VBtn>
                      </td>

                      <!-- Experience -->
                      <td class="text-no-wrap" width="70">
                        <h4>Experience:</h4>
                      </td>
                      <td
                        class="text-right"
                        v-if="editing.field != 'experience'"
                      >
                        {{ lead.experience || '-' }}
                      </td>
                      <td v-else>
                        <VTextField
                          v-model="editing.value"
                          type="text"
                          color="primary"
                          append-icon="tabler-x"
                          @click:append="setEditing"
                        />
                      </td>
                      <td class="text-right" width="70">
                        <VBtn
                          icon
                          variant="text"
                          color="default"
                          size="small"
                          class="text-high-emphasis ms-n1 edit"
                          :class="{ 'd-none': editing.field == 'experience' || editing.loading }"
                          @click="setEditing('experience')"
                        >
                          <VIcon
                            size="22"
                            icon="tabler-edit"
                          />
                        </VBtn>
                        <VBtn
                          color="primary"
                          size="small"
                          v-show="editing.field == 'experience'"
                          :loading="editing.loading"
                          :disabled="editing.loading"
                          @click="updateField"
                        >
                          SAVE
                        </VBtn>
                      </td>
                    </tr>
                  </tbody>
                </VTable>

                <VTable>
                  <tbody>
                    <tr>
                      <!-- Arrange after employemt -->
                      <td class="text-no-wrap" width="70">
                        <h4>Arrange after employemt:</h4>
                      </td>
                      <td
                        class="text-right"
                        v-if="editing.field != 'arrange_after_employment'"
                      >
                        {{ lead.arrange_after_employment || '-' }}
                      </td>
                      <td v-else>
                        <VTextField
                          v-model="editing.value"
                          type="text"
                          color="primary"
                          append-icon="tabler-x"
                          @click:append="setEditing"
                        />
                      </td>
                      <td class="text-right" width="70">
                        <VBtn
                          icon
                          variant="text"
                          color="default"
                          size="small"
                          class="text-high-emphasis ms-n1 edit"
                          :class="{ 'd-none': editing.field == 'arrange_after_employment' || editing.loading }"
                          @click="setEditing('arrange_after_employment')"
                        >
                          <VIcon
                            size="22"
                            icon="tabler-edit"
                          />
                        </VBtn>
                        <VBtn
                          color="primary"
                          size="small"
                          v-show="editing.field == 'arrange_after_employment'"
                          :loading="editing.loading"
                          :disabled="editing.loading"
                          @click="updateField"
                        >
                          SAVE
                        </VBtn>
                      </td>
                    </tr>
                  </tbody>
                </VTable>

                <VTable>
                  <tbody>
                    <tr>
                      <td class="text-no-wrap">
                        <span><strong>Product type: </strong>{{ lead.product_str }}</span>
                      </td>
                      <td class="text-end">
                        <VChip
                          color="primary"
                          variant="elevated"
                        >
                          {{ lead.status_str }}
                        </VChip>
                      </td>
                      <td class="text-right" width="70"></td>
                    </tr>
                    
                    <tr>
                      <td>
                        <span><strong>Status: </strong>{{ lead.reason ? `${lead.reason.status} - ${lead.reason.reason}` : 'N/A'  }}</span>
                      </td>
                      
                      <td class="text-end">
                        <h4>
                          Callback:
                          <VChip
                          class="ms-2"
                            color="primary"
                            variant="elevated"
                            @click="openCallBack"
                          >
                            {{ lead.callback.date || 'SET CALLBACK' }}
                          </VChip>
                        </h4>
                      </td>
                      <td class="text-right" width="70"></td>
                    </tr>
                    
                    <tr>
                      <td>
                        <span><strong>Paid amount: </strong> {{ lead.paid_str }}</span>
                      </td>
                      <td class="text-end">
                        <span><strong>Quote amount: </strong> {{ lead.amount_str }}</span>
                      </td>
                      <td class="text-right" width="70"></td>
                    </tr>
                    
                    <tr>
                      <td>
                        <span><strong>Active: </strong> Yes</span>
                      </td>
                      <td class="text-end">
                        <span><strong>Retainer signed at: </strong> {{ lead.retainer ? lead.retainer.signed_at : 'N/A' }}</span>
                      </td>
                      <td class="text-right" width="70"></td>
                    </tr>
                  </tbody>
                </VTable>
              </VWindowItem>

              <VWindowItem>
                <VRow class="mt-4">
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="spouse.firstname"
                      label="First Name"
                      type="text"
                      color="primary"
                      class="mb-2"
                    />
                  </VCol>

                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="spouse.lastname"
                      label="Last Name"
                      type="text"
                      color="primary"
                      class="mb-2"
                    />
                  </VCol>

                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="spouse.dob"
                      label="Date of birth"
                      type="text"
                      color="primary"
                      class="mb-2"
                    />
                  </VCol>

                  <VCol cols="12" md="6">
                    <VSelect
                      v-model="spouse.gender"
                      label="Gender"
                      :items="genders"
                      item-title="text"
                      item-value="value"
                      class="mb-2"
                    />
                  </VCol>

                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="spouse.dob"
                      label="Date of birth"
                      type="text"
                      color="primary"
                      class="mb-2"
                    />
                  </VCol>

                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="spouse.phone_number"
                      label="Mobile Phone"
                      type="text"
                      color="primary"
                      class="mb-2"
                    />
                  </VCol> 

                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="spouse.email"
                      label="Email"
                      type="text"
                      color="primary"
                      class="mb-2"
                    />
                  </VCol>
                </VRow>

                <VBtn
                  :loading="spouseLoading"
                  :disabled="spouseLoading"
                  @click="saveSpouse"
                >
                  Save
                </VBtn>
              </VWindowItem>
            </VWindow>

            </VCardText>
          </VCard>
        </VCol>

        <VCol cols="12" md="4">
          <div
            v-if="!props.backoffice"
          >
            <VBtn
              style="width: 39%;"
              color="error"
              class="mb-4"
              size="small"
              :disabled="holdVisible"
            >
              Disqualify
              <VMenu activator="parent">
                <VList>
                  <VListItem @click="setReason('Disqualified', 'No English')">
                    <VListItemTitle>No English</VListItemTitle>
                  </VListItem>
                  <VListItem @click="setReason('Disqualified', 'No Interest')">
                    <VListItemTitle>No Interest</VListItemTitle>
                  </VListItem>
                  <VListItem @click="setReason('Disqualified', 'No High School')">
                    <VListItemTitle>No High School</VListItemTitle>
                  </VListItem>
                  <VListItem @click="setReason('Disqualified', 'Insufficient Funds')">
                    <VListItemTitle>Insufficient Funds</VListItemTitle>
                  </VListItem>
                  <VListItem @click="setReason('Disqualified', 'Medical Condition')">
                    <VListItemTitle>Medical Condition</VListItemTitle>
                  </VListItem>
                  <VListItem @click="setReason('Disqualified', 'Not Eligible')">
                    <VListItemTitle>Not Eligible</VListItemTitle>
                  </VListItem>
                </VList>
              </VMenu>
            </VBtn>

            <VBtn
              style="width: 29%; margin-left: 1.5%;"
              color="warning"
              class="mb-4"
              size="small"
              @click="toggleHold"
            >
              {{ holdVisible ? 'Cancel' : 'Hold' }}
            </VBtn>

            <VBtn
              style="width: 29%; margin-left: 1.5%;"
              color="error"
              class="mb-4"
              size="small"
              :disabled="holdVisible"
            >
              <VIcon
                size="22"
                icon="tabler-alert-triangle"
              />
              Risk
              <VMenu activator="parent">
                <VList>
                  <VListItem @click="setReason('Risk', 'Asked for refund')">
                    <VListItemTitle>Asked for refund</VListItemTitle>
                  </VListItem>
                  <VListItem @click="setReason('Risk', 'Chargeback')">
                    <VListItemTitle>Chargeback</VListItemTitle>
                  </VListItem>
                </VList>
              </VMenu>
            </VBtn>

            <VRow
              class="mb-2"
              v-show="holdVisible"
            >
              <VCol cols="12" sm="8">
                <VTextField
                  v-model="holdReason"
                  label="Hold reason"
                  placeholder="Hold reason"
                />
              </VCol>

              <VCol cols="12" sm="4">
                <VBtn
                  style="width: 100%;"
                  color="primary"
                  @click="setReason('Hold', holdReason)"
                  :disabled="holdLoading"
                  :loading="holdLoading"
                >
                  PUT ON HOLD
                </VBtn>
              </VCol>
            </VRow>

            <VBtn
              color="primary"
              style="width: 49%;"
              class="mb-4"
              @click="openCallBack"
            >
              SET CALLBACK
            </VBtn>

            <VBtn
              color="success"
              style="width: 49%; margin-left: 2%;"
              class="mb-4"
              @click="recall"
              :loading="recallLoading"
              :disabled="recallLoading"
            >
              MOVE TO RE-CALL
            </VBtn>
          </div>

          <VCard
            class="mb-4"
            v-if="props.backoffice && !cs"
          >
            <VCardText>
              <input @change="uploadDoc2" class="d-none" type="file" id="files2" accept=".pdf" ref="files2" />
              <VList class="card-list">
                <VListItem
                  v-for="(bo, name) in backoffice"
                  :key="name"
                >
                  <VListItemTitle class="font-weight-medium">
                    {{ name }}
                  </VListItemTitle>

                  <template #append>
                    <VBtn
                      icon="tabler-cloud-upload"
                      variant="text"
                      color="success"
                      @click="uploadFile2(name)"
                      :loading="bo.loading"
                      :disabled="bo.loading"
                      v-if="!bo.doc"
                    />

                    <VBtn
                      v-else
                      icon="tabler-eye"
                      variant="text"
                      color="success"
                      @click="openDoc(bo.doc)"
                    />

                    <VBtn
                      rounded="pill"
                      :color="backOfficeDocColor(bo.status)"
                      size="small"
                    >
                      {{ bo.status }}
                      <VMenu activator="parent">
                        <VList>
                          <VListItem
                            @click="setBackOfficeStatus(bo, name, 'pre-approved')"
                          >
                            PRE-APPROVED
                          </VListItem>
                          <VListItem
                            @click="setBackOfficeStatus(bo, name, 'required')"
                          >
                            REQUIRED
                          </VListItem>
                          <VListItem
                            @click="setBackOfficeStatus(bo, name, 'review')"
                          >
                            REVIEW
                          </VListItem>
                          <VListItem
                            @click="setBackOfficeStatus(bo, name, 'optional')"
                          >
                            OPTIONAL
                          </VListItem>
                        </VList>
                      </VMenu>
                    </VBtn>
                  </template>
                </VListItem>
              </VList>
            </VCardText>
          </VCard>

          <VExpansionPanels v-model="panel">
            <VExpansionPanel>
              <VExpansionPanelTitle>
                Payment history
              </VExpansionPanelTitle>
              <VExpansionPanelText>
                <div style="overflow: auto;max-height: 350px;">
                  <PaymentLinkTimeline :payments="payments"/>
                </div>
              </VExpansionPanelText>
            </VExpansionPanel>

            <VExpansionPanel>
              <VExpansionPanelTitle>
                Comments
              </VExpansionPanelTitle>
              <VExpansionPanelText>
                <div style="overflow: auto;max-height: 350px;">
                  <VTextarea
                    v-model="newComment"
                    label="New comment"
                    class="mt-2"
                    auto-grow
                  />
                  <VBtn
                    color="primary" 
                    class="mt-2"
                    :loading="commentLoading"
                    :disabled="commentLoading || newComment.trim() == ''"
                    @click="makeComment"
                  >
                    Save
                  </VBtn>
                  <div class="mt-4">
                    <div
                      style="border: #eee solid 1px; border-radius: 5px;"
                      class="pa-2 mb-2"
                      v-for="comment in comments"
                    >
                      <span
                        class="text-sm text-disabled"
                        v-if="comment.agent"
                      >
                        {{ comment.agent }}
                      </span>
                      <div class="mt-2">
                        {{ comment.comment }}
                      </div>
                      <span class="text-sm text-disabled">
                        <i>{{ comment.date }}</i>
                      </span>
                    </div>
                  </div>

                </div>
              </VExpansionPanelText>
            </VExpansionPanel>

            <VExpansionPanel>
              <VExpansionPanelTitle>
                Documents
              </VExpansionPanelTitle>
              <VExpansionPanelText>
                <VBtn
                  color="primary"
                  style="width: 100%;"
                  class="mb-4"
                  @click="uploadFile"
                  :loading="uploadLoading"
                  :disabled="uploadLoading"
                >
                  <VIcon
                    start
                    icon="tabler-upload"
                  />
                  UPLOAD DOC
                </VBtn>
                <input @change="uploadDoc" class="d-none" type="file" id="files" accept=".pdf" ref="files" multiple />
                <div style="overflow: auto;max-height: 350px;">
                  <div
                    style="border: #eee solid 1px; border-radius: 5px;"
                    class="pa-2 mb-2"
                    v-for="document in documents"
                  >
                    <span
                      class="text-sm text-disabled"
                      v-if="document.agent"
                    >
                      {{ document.agent }}
                    </span>
                    <div class="mt-2">
                      {{ document.name }}
                    </div>
                    <span class="text-sm text-disabled">
                      <i>{{ document.date }}</i>
                    </span>
                    <div class="demo-space-x">
                      <VBtn variant="text">
                        <a :href="document.url" target="_blank">View</a>
                      </VBtn>

                      <VBtn
                        variant="text"
                        color="error"
                        @click="confirmDocRemovel(document.id)"
                      >
                        Delete
                      </VBtn>
                    </div>
                  </div>
                </div>
              </VExpansionPanelText>
            </VExpansionPanel>
          </VExpansionPanels>
        </VCol>
      </VRow>

      <!-- Payment link -->
      <VDialog
        v-model="isDialogVisible"
        persistent
        width="500"
      >
        <DialogCloseBtn @click="isDialogVisible = !isDialogVisible" />
        <VCard>
          <VForm v-model="isValid" @submit.prevent="() => {}">
            <VCardText>
              <VSelect
                v-model="form.method"
                :items="paymentMethods"
                :rules="[requiredValidator]"
                item-title="text"
                item-value="value"
                label="Payment method"
                class="mt-6"
              />   

              <VSelect
                v-model="form.lan"
                :items="languages"
                :rules="[requiredValidator]"
                item-title="text"
                item-value="value"
                label="Language"
                class="mt-6"
              />  

              <VSelect
                v-model="form.sendVia"
                :items="['Email', 'SMS']"
                :rules="[requiredValidator]"
                label="Send via"
                class="mt-6"
              /> 

            <VSelect
              class="mt-6"
              v-model="product"
              :items="products"
              item-title="text"
              item-value="value"
              :rules="[requiredValidator]"
              label="Product type"
            /> 

              <VTextField
                v-model="email"
                v-show="form.sendVia == 'Email'"
                :rules="[requiredValidator, emailValidator]"
                label="Email"
                class="mt-6"
              />

              <VTextField
                v-model="phone_number"
                v-show="form.sendVia == 'SMS'"
                :rules="[requiredValidator]"
                label="Phone number"
                class="mt-6"
              />

              <VTextField
                class="mt-6"
                v-model="qAmount"
                label="Quote amount"
                disabled
              />

              <VTextField
                v-model="form.amount"
                :label="`Outstanding - ${lead.outstanding_str}`"
                :rules="[requiredValidator, rules.greaterAmount]"
                placeholder="How much to charge now?"
                class="mt-6"
              />
            </VCardText>

            <VCardText class="d-flex justify-end">
              <VBtn
                type="submit"
                :loading="linkLoading"
                :disabled="linkLoading"
                @click="paymentLink"
              >
                Send Payment
              </VBtn>
            </VCardText>
          </VForm>
        </VCard>
      </VDialog>
      
      <!-- Send document -->
      <VDialog
        v-model="isDocDialogVisible"
        persistent
        width="500"
      >
        <DialogCloseBtn @click="isDocDialogVisible = !isDocDialogVisible" />
        <VCard>

          <VForm v-model="isValid2" @submit.prevent="() => {}">
            <VCardText>
              <VTextField
                v-model="lead.email"
                :rules="[requiredValidator, emailValidator]"
                label="Email"
                class="mt-6"
              />
            </VCardText>

            <VCardText class="d-flex justify-end">
              <VBtn
                variant="text"
              >
                <a :href="lead.retainer.file" target="_blank">retainer</a>
              </VBtn>
              <VBtn
                variant="text"
              >
              results
              <VMenu activator="parent">
                <VList>
                  <VListItem
                    v-for="(result, i) in lead.results"
                    :title="result.name"
                    @click="viewResult(result.file)"
                  />
                </VList>
              </VMenu>

              </VBtn>
              
              <VBtn
                type="submit"
                :loading="linkLoading"
                :disabled="linkLoading"
                @click="contractLink"
              >
                Send results
              </VBtn>
            </VCardText>
          </VForm>
        </VCard>
      </VDialog>
      
      <!-- Creds dialog -->
      <VDialog
        v-model="isCredsDialogVisible"
        persistent
        width="500"
      >
        <DialogCloseBtn @click="isCredsDialogVisible = !isCredsDialogVisible" />
        <VCard>
          <VCardText>
            <VTextField
              v-model="lead.email"
              :rules="[requiredValidator, emailValidator]"
              label="Email"
              class="mt-6"
            />
          </VCardText>

          <VCardText v-if="creds">
            <strong>Email: </strong> <i>{{ creds.email }}</i><br>
            <strong>Password: </strong> <i>{{ creds.password }}</i>
          </VCardText>

          <VCardText class="d-flex justify-end">
            <VBtn
              variant="text"
              :loading="credsLoading"
              :disabled="credsLoading"
              @click="viewCreds"
            >
              View Details
            </VBtn>
            
            <VBtn
              type="submit"
              :loading="credsLoading"
              :disabled="credsLoading"
              @click="sendCreds"
            >
              Send details
            </VBtn>
          </VCardText>
        </VCard>
      </VDialog>

      <!-- Call back -->
      <VDialog
        v-model="isCallBackDialogVisible"
        persistent
        width="500"
      >
        <DialogCloseBtn @click="isCallBackDialogVisible = !isCallBackDialogVisible" />
        <VCard
          :title="`${lead.callback.agent}'s callback` || 'Set callback'"
        >
          <VCardText>
            <AppDateTimePicker
              v-model="date"
              label="Date & Time"
              :config="{ inline: true, enableTime: true, dateFormat: 'F j, Y H:i' }"
            />
          </VCardText>
          <VCardText class="d-flex">
            <VBtn
              type="submit"
              :loading="callBackLoading"
              :disabled="callBackLoading || date == ''"
              @click="callBack()"
            >
              Set call back
            </VBtn>
          </VCardText>
        </VCard>
      </VDialog>

      <VDialog
        v-model="removeDocDialogVisible"
        persistent
        class="v-dialog-sm"
      >
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="removeDocDialogVisible = !removeDocDialogVisible" />

        <!-- Dialog Content -->
        <VCard title="Remove document?">
          <VCardText>
            You are about to delete a document.
          </VCardText>

          <VCardText class="d-flex justify-end gap-3 flex-wrap">
            <VBtn
              color="secondary"
              variant="tonal"
              @click="removeDocDialogVisible = false"
            >
              Close
            </VBtn>
            <VBtn
              @click="removeDocument"
              :disabled="removeDocLoading"
              :loading="removeDocLoading"
            >
              Confirm
            </VBtn>
          </VCardText>
        </VCard>
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

<style scoped>
.edit {
  display: none;
}

td {
  min-width: 100px;
}

tr:hover .edit {
  display: inline-grid;
}

</style>