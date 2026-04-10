<script setup>
import DigitalPhoto from '@/components/customer/AwaitingLOA/DigitalPhoto.vue'
import PassportCopy from '@/components/customer/AwaitingLOA/PassportCopy.vue'

const awaitingLOAProgress = ref(0)
const openedPanel = ref(null)
const props = defineProps({
  documents: {
    type: Object,
    required: true,
  },
})

const backOfficeDocColor = (name) => {
  console.log();
  const doc = props.documents[name]
  if(doc) {
    if(doc.status == 'pre-approved') return 'success'
    if(doc.status == 'required') return 'error'
    if(doc.status == 'optional') return 'primary'
    if(doc.status == 'review') return 'info'
  }
  return 'error' 
}

const backOfficeDocStatus = (name) => {
  const doc = props.documents[name]
  return doc ? doc.status.toUpperCase() : 'REQUIRED'
}

const docName = (name) => {
  const doc = props.documents[name]
  return doc ? doc.name : null
}

let slug = ''
const uploadFile2 = (_slug) => {
  slug = _slug
  document.getElementById('files2').click()
}

const emit = defineEmits([
  'refresh',
])

const uploadDoc2 = e => {
  const files = e.target.files || e.dataTransfer.files
  if(files.length) {
    const formData = new FormData();
    formData.append('file', files[0]);
    formData.append('name', files[0].name);

    console.log(slug);
    const headers = { 'Content-Type': 'multipart/form-data' }
    axios
      .post(`/api/customers/doc/${slug}`, formData, { headers })
      .then(res => emit('refresh'))
      .catch((e) => { })
  }
}

</script>

<template>
  <VCard title="All Products" variant="tonal">
    <input @change="uploadDoc2" class="d-none" type="file" id="files2" accept=".pdf" ref="files2" />
    <template #prepend>
      <VProgressCircular
        :rotate="360"
        :size="40"
        :width="6"
        :model-value="awaitingLOAProgress"
        color="primary"
      >
        {{ awaitingLOAProgress }}
      </VProgressCircular>
    </template>

    <VCardText>
      <VExpansionPanels
        v-model="openedPanel"
        class="expansion-panels-width-border"
      >
        <!-- DIGITAL PHOTO (LIKE THE ONE IN YOUR PASSPORT) -->
        <VExpansionPanel elevation="0">
          <VExpansionPanelTitle
            :color="openedPanel == 0 ? 'primary' : ''"
          >
            <div
              class="me-2 pa-1"
              style="background-color: #fff; border-radius: 5px;"
            >
              <VChip
                label
                :color="backOfficeDocColor('DIGITAL PHOTO (LIKE THE ONE IN YOUR PASSPORT)')"
                style="border: 1px solid #fff;"
              >
                {{ backOfficeDocStatus('DIGITAL PHOTO (LIKE THE ONE IN YOUR PASSPORT)') }}
              </VChip>
            </div>
            PHOTOGRAPH ID SIZE 
          </VExpansionPanelTitle>
          <VExpansionPanelText>
            <DigitalPhoto
              :name="docName('DIGITAL PHOTO (LIKE THE ONE IN YOUR PASSPORT)')"
              @upload:doc="uploadFile2('DIGITAL PHOTO (LIKE THE ONE IN YOUR PASSPORT)')"
            />
          </VExpansionPanelText>
        </VExpansionPanel>

        <!-- PASSPORT COPY -->
        <VExpansionPanel elevation="0">
          <VExpansionPanelTitle
            :color="openedPanel == 1 ? 'primary' : ''"
          >
            <div
              class="me-2 pa-1"
              style="background-color: #fff; border-radius: 5px;"
            >
              <VChip
                label
                :color="backOfficeDocColor('PASSPORT COPY')"
                style="border: 1px solid #fff;"
              >
                {{ backOfficeDocStatus('PASSPORT COPY') }}
              </VChip>
            </div>
            COPY OF PASSPORT OR ID
          </VExpansionPanelTitle>
          <VExpansionPanelText>
            <PassportCopy
              :name="docName('PASSPORT COPY')"
              @upload:doc="uploadFile2('PASSPORT COPY')"
            />
          </VExpansionPanelText>
        </VExpansionPanel>

        <!-- UTILITY BILL -->
        <VExpansionPanel elevation="0">
          <VExpansionPanelTitle
            :color="openedPanel == 2 ? 'primary' : ''"
          >
            <div
              class="me-2 pa-1"
              style="background-color: #fff; border-radius: 5px;"
            >
              <VChip
                label
                :color="backOfficeDocColor('UTILITY BILL')"
                style="border: 1px solid #fff;"
              >
                {{ backOfficeDocStatus('UTILITY BILL') }}
              </VChip>
            </div>
            UTILITY BILL
          </VExpansionPanelTitle>
          <VExpansionPanelText>
            <VRow
              style="background-color: #eee;"
              class="ma-0 mt-4 mb-2"
            >
              <VCol
                cols="12"
                sm="6"
                style="background-color: #fff;"
              >
              -
              </VCol>

              <VCol
                cols="12"
                sm="6"
                class="mb-2 text-center"
              >
                <div class="mt-4">
                  <i> {{ docName('UTILITY BILL') || 'Your Documents' }}</i>
                  <br>
                  <br>
                  <VBtn
                    color="primary"
                    @click="uploadFile2('UTILITY BILL')"
                  >
                    <VIcon
                      size="22"
                      icon="tabler-cloud-upload"
                    />
                    {{ docName('UTILITY BILL') ? ' Replace' : ' Upload' }}
                  </VBtn>
                </div>
              </VCol>
            </VRow>
          </VExpansionPanelText>
        </VExpansionPanel>

        <!-- CV -->
        <VExpansionPanel elevation="0">
          <VExpansionPanelTitle
            :color="openedPanel == 3 ? 'primary' : ''"
          >
            <div
              class="me-2 pa-1"
              style="background-color: #fff; border-radius: 5px;"
            >
              <VChip
                label
                :color="backOfficeDocColor('CV')"
                style="border: 1px solid #fff;"
              >
                {{ backOfficeDocStatus('CV') }}
              </VChip>
            </div>
            CV
          </VExpansionPanelTitle>
          <VExpansionPanelText>
            <VRow
              style="background-color: #eee;"
              class="ma-0 mt-4 mb-2"
            >
              <VCol
                cols="12"
                sm="6"
                style="background-color: #fff;"
              >
              -
              </VCol>

              <VCol
                cols="12"
                sm="6"
                class="mb-2 text-center"
              >
                <div class="mt-4">
                  <i> {{ docName('CV') || 'Your Documents' }}</i>
                  <br>
                  <br>
                  <VBtn
                    color="primary"
                    @click="uploadFile2('CV')"
                  >
                    <VIcon
                      size="22"
                      icon="tabler-cloud-upload"
                    />
                    {{ docName('CV') ? ' Replace' : ' Upload' }}
                  </VBtn>
                </div>
              </VCol>
            </VRow>
          </VExpansionPanelText>
        </VExpansionPanel>

        <!-- COPY OF DIPLOMA or DEGREE or TRANSCRIPTS -->
        <VExpansionPanel elevation="0">
          <VExpansionPanelTitle
            :color="openedPanel == 4 ? 'primary' : ''"
          >
            <div
              class="me-2 pa-1"
              style="background-color: #fff; border-radius: 5px;"
            >
              <VChip
                label
                :color="backOfficeDocColor('COPY OF DIPLOMA or DEGREE or TRANSCRIPTS')"
                style="border: 1px solid #fff;"
              >
                {{ backOfficeDocStatus('COPY OF DIPLOMA or DEGREE or TRANSCRIPTS') }}
              </VChip>
            </div>
            EDUCATION CERTIFICATE
          </VExpansionPanelTitle>
          <VExpansionPanelText>
            <VRow
              style="background-color: #eee;"
              class="ma-0 mt-4 mb-2"
            >
              <VCol
                cols="12"
                sm="6"
                style="background-color: #fff;"
              >
              <p>Please upload a copy of your most recent educational qualification</p>
              </VCol>

              <VCol
                cols="12"
                sm="6"
                class="mb-2 text-center"
              >
                <div class="mt-4">
                  <i> {{ docName('COPY OF DIPLOMA or DEGREE or TRANSCRIPTS') || 'Your Documents' }}</i>
                  <br>
                  <br>
                  <VBtn
                    color="primary"
                    @click="uploadFile2('COPY OF DIPLOMA or DEGREE or TRANSCRIPTS')"
                  >
                    <VIcon
                      size="22"
                      icon="tabler-cloud-upload"
                    />
                    {{ docName('COPY OF DIPLOMA or DEGREE or TRANSCRIPTS') ? ' Replace' : ' Upload' }}
                  </VBtn>
                </div>
              </VCol>
            </VRow>
          </VExpansionPanelText>
        </VExpansionPanel>
      </VExpansionPanels>
    </VCardText>
  </VCard>
</template>