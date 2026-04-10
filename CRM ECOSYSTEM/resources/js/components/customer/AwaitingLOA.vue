<script setup>
import RetainerAgreement from '@/components/customer/AwaitingLOA/RetainerAgreement.vue'
import LetterOfAcceptance from '@/components/customer/AwaitingLOA/LetterOfAcceptance.vue'
import EmploymentLetter from '@/components/customer/AwaitingLOA/EmploymentLetter.vue'
import DigitalPhoto from '@/components/customer/AwaitingLOA/DigitalPhoto.vue'
import StudentVisa from '@/components/customer/AwaitingLOA/StudentVisa.vue'
import PassportCopy from '@/components/customer/AwaitingLOA/PassportCopy.vue'
import FamilyRelationshipDocument from '@/components/customer/AwaitingLOA/FamilyRelationshipDocument.vue'
import Affidavit from '@/components/customer/AwaitingLOA/Affidavit.vue'
import ReferenceLetter from '@/components/customer/AwaitingLOA/ReferenceLetter.vue'
import ProofOfTies from '@/components/customer/AwaitingLOA/ProofOfTies.vue'
import StudyPlan from '@/components/customer/AwaitingLOA/StudyPlan.vue'
import Diploma from '@/components/customer/AwaitingLOA/Diploma.vue'
import IELTS from '@/components/customer/AwaitingLOA/IELTS.vue'

const awaitingLOAProgress = ref(0)
const openedPanel = ref(null)

const props = defineProps({
  loa: {
    type: Object,
    required: true,
  },
  documents: {
    type: Object,
    required: true,
  },
})

const backOfficeDocColor = (name) => {
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

    const headers = { 'Content-Type': 'multipart/form-data' }
    axios
      .post(`/api/customers/doc/${slug}`, formData, { headers })
      .then(res => emit('refresh'))
      .catch((e) => { })
  }
}

</script>

<template>
  <VCard title="Awaiting LOA" variant="tonal">
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
        <!-- RETAINER AGREEMENT -->
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
                style="border: 1px solid #fff;"
              ></VChip>
            </div>
            RETAINER AGREEMENT
          </VExpansionPanelTitle>
          <VExpansionPanelText>
            <RetainerAgreement :retainer="props.loa.retainer" />
          </VExpansionPanelText>
        </VExpansionPanel>

        <!-- LETTER OF ACCEPTANCE -->
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
                :color="backOfficeDocColor('LETTER OF ACCEPTANCE & RECEIPT OF TUITIONS OR FEES CONTRIBUTED')"
                style="border: 1px solid #fff;"
              >
                {{ backOfficeDocStatus('LETTER OF ACCEPTANCE & RECEIPT OF TUITIONS OR FEES CONTRIBUTED') }}
              </VChip>
            </div>
            LETTER OF ACCEPTANCE & RECEIPT OF TUITIONS OR FEES CONTRIBUTED
          </VExpansionPanelTitle>
          <VExpansionPanelText>
            <LetterOfAcceptance
              :name="docName('LETTER OF ACCEPTANCE & RECEIPT OF TUITIONS OR FEES CONTRIBUTED')"
              @upload:doc="uploadFile2('LETTER OF ACCEPTANCE & RECEIPT OF TUITIONS OR FEES CONTRIBUTED')"
            />
          </VExpansionPanelText>
        </VExpansionPanel>

        <!-- EMPLOYMENT LETTER -->
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
                :color="backOfficeDocColor('EMPLOYMENT LETTER')"
                style="border: 1px solid #fff;"
              >
                {{ backOfficeDocStatus('EMPLOYMENT LETTER') }}
              </VChip>
            </div>
            EMPLOYMENT LETTER
          </VExpansionPanelTitle>
          <VExpansionPanelText>
            <EmploymentLetter
              :name="docName('EMPLOYMENT LETTER')"
              @upload:doc="uploadFile2('EMPLOYMENT LETTER')"
            />
          </VExpansionPanelText>
        </VExpansionPanel>

        <!-- IELTS -->
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
                :color="backOfficeDocColor('IELTS')"
                style="border: 1px solid #fff;"
              >
                {{ backOfficeDocStatus('IELTS') }}
              </VChip>
            </div>
            IELTS
          </VExpansionPanelTitle>
          <VExpansionPanelText>
            <IELTS
              :name="docName('IELTS')"
              @upload:doc="uploadFile2('IELTS')"
            />
          </VExpansionPanelText>
        </VExpansionPanel>

        <!-- DIGITAL PHOTO (LIKE THE ONE IN YOUR PASSPORT) -->
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
                :color="backOfficeDocColor('DIGITAL PHOTO (LIKE THE ONE IN YOUR PASSPORT)')"
                style="border: 1px solid #fff;"
              >
                {{ backOfficeDocStatus('DIGITAL PHOTO (LIKE THE ONE IN YOUR PASSPORT)') }}
              </VChip>
            </div>
            DIGITAL PHOTO (LIKE THE ONE IN YOUR PASSPORT)
          </VExpansionPanelTitle>
          <VExpansionPanelText>
            <DigitalPhoto
              :name="docName('DIGITAL PHOTO (LIKE THE ONE IN YOUR PASSPORT)')"
              @upload:doc="uploadFile2('DIGITAL PHOTO (LIKE THE ONE IN YOUR PASSPORT)')"
            />
          </VExpansionPanelText>
        </VExpansionPanel>

        <!-- STUDENT VISA ELIGIBILITY FORM -->
        <VExpansionPanel elevation="0">
          <VExpansionPanelTitle
            :color="openedPanel == 5 ? 'primary' : ''"
          >
            <div
              class="me-2 pa-1"
              style="background-color: #fff; border-radius: 5px;"
            >
              <VChip
                label
                :color="backOfficeDocColor('STUDENT VISA ELIGIBILITY FORM')"
                style="border: 1px solid #fff;"
              >
                {{ backOfficeDocStatus('STUDENT VISA ELIGIBILITY FORM') }}
              </VChip>
            </div>
            STUDENT VISA ELIGIBILITY FORM
          </VExpansionPanelTitle>
          <VExpansionPanelText>
            <StudentVisa
              :name="docName('STUDENT VISA ELIGIBILITY FORM')"
              @upload:doc="uploadFile2('STUDENT VISA ELIGIBILITY FORM')"
            />
          </VExpansionPanelText>
        </VExpansionPanel>

        <!-- PASSPORT COPY -->
        <VExpansionPanel elevation="0">
          <VExpansionPanelTitle
            :color="openedPanel == 6 ? 'primary' : ''"
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
            PASSPORT COPY
          </VExpansionPanelTitle>
          <VExpansionPanelText>
            <PassportCopy
              :name="docName('PASSPORT COPY')"
              @upload:doc="uploadFile2('PASSPORT COPY')"
            />
          </VExpansionPanelText>
        </VExpansionPanel>

        <!-- FAMILY RELATIONSHIP DOCUMENT -->
        <VExpansionPanel elevation="0">
          <VExpansionPanelTitle
            :color="openedPanel == 7 ? 'primary' : ''"
          >
            <div
              class="me-2 pa-1"
              style="background-color: #fff; border-radius: 5px;"
            >
              <VChip
                label
                :color="backOfficeDocColor('FAMILY RELATIONSHIP DOCUMENT')"
                style="border: 1px solid #fff;"
              >
                {{ backOfficeDocStatus('FAMILY RELATIONSHIP DOCUMENT') }}
              </VChip>
            </div>
            FAMILY RELATIONSHIP DOCUMENT
          </VExpansionPanelTitle>
          <VExpansionPanelText>
            <FamilyRelationshipDocument
              :name="docName('FAMILY RELATIONSHIP DOCUMENT')"
              @upload:doc="uploadFile2('FAMILY RELATIONSHIP DOCUMENT')"
            />
          </VExpansionPanelText>
        </VExpansionPanel>

        <!-- AFFIDAVIT LETTER -->
        <VExpansionPanel elevation="0">
          <VExpansionPanelTitle
            :color="openedPanel == 8 ? 'primary' : ''"
          >
            <div
              class="me-2 pa-1"
              style="background-color: #fff; border-radius: 5px;"
            >
              <VChip
                label
                :color="backOfficeDocColor('AFFIDAVIT LETTER')"
                style="border: 1px solid #fff;"
              >
                {{ backOfficeDocStatus('AFFIDAVIT LETTER') }}
              </VChip>
            </div>
            AFFIDAVIT LETTER
          </VExpansionPanelTitle>
          <VExpansionPanelText>
            <Affidavit
              :name="docName('AFFIDAVIT LETTER')"
              @upload:doc="uploadFile2('AFFIDAVIT LETTER')"
            />
          </VExpansionPanelText>
        </VExpansionPanel>

        <!-- REFERENCE LETTER FROM FRIENDS AND FAMILY -->
        <VExpansionPanel elevation="0">
          <VExpansionPanelTitle
            :color="openedPanel == 9 ? 'primary' : ''"
          >
            <div
              class="me-2 pa-1"
              style="background-color: #fff; border-radius: 5px;"
            >
              <VChip
                label
                :color="backOfficeDocColor('REFERENCE LETTER FROM FRIENDS AND FAMILY')"
                style="border: 1px solid #fff;"
              >
                {{ backOfficeDocStatus('REFERENCE LETTER FROM FRIENDS AND FAMILY') }}
              </VChip>
            </div>
            REFERENCE LETTER FROM FRIENDS AND FAMILY
          </VExpansionPanelTitle>
          <VExpansionPanelText>
            <ReferenceLetter
              :name="docName('REFERENCE LETTER FROM FRIENDS AND FAMILY')"
              @upload:doc="uploadFile2('REFERENCE LETTER FROM FRIENDS AND FAMILY')"
            />
          </VExpansionPanelText>
        </VExpansionPanel>

        <!-- PROOF OF TIES TO HOME COUNTRY -->
        <VExpansionPanel elevation="0">
          <VExpansionPanelTitle
            :color="openedPanel == 10 ? 'primary' : ''"
          >
            <div
              class="me-2 pa-1"
              style="background-color: #fff; border-radius: 5px;"
            >
              <VChip
                label
                :color="backOfficeDocColor('PROOF OF TIES TO HOME COUNTRY')"
                style="border: 1px solid #fff;"
              >
                {{ backOfficeDocStatus('PROOF OF TIES TO HOME COUNTRY') }}
              </VChip>
            </div>
            PROOF OF TIES TO HOME COUNTRY
          </VExpansionPanelTitle>
          <VExpansionPanelText>
            <ProofOfTies
              :name="docName('PROOF OF TIES TO HOME COUNTRY')"
              @upload:doc="uploadFile2('PROOF OF TIES TO HOME COUNTRY')"
            />
          </VExpansionPanelText>
        </VExpansionPanel>

        <!-- STUDY PLAN -->
        <VExpansionPanel elevation="0">
          <VExpansionPanelTitle
            :color="openedPanel == 11 ? 'primary' : ''"
          >
            <div
              class="me-2 pa-1"
              style="background-color: #fff; border-radius: 5px;"
            >
              <VChip
                label
                :color="backOfficeDocColor('STUDY PLAN')"
                style="border: 1px solid #fff;"
              >
                {{ backOfficeDocStatus('STUDY PLAN') }}
              </VChip>
            </div>
            STUDY PLAN
          </VExpansionPanelTitle>
          <VExpansionPanelText>
            <StudyPlan
              :name="docName('STUDY PLAN')"
              @upload:doc="uploadFile2('STUDY PLAN')"
            />
          </VExpansionPanelText>
        </VExpansionPanel>

        <!-- COPY OF DIPLOMA or DEGREE or TRANSCRIPTS -->
        <VExpansionPanel elevation="0">
          <VExpansionPanelTitle
            :color="openedPanel == 12 ? 'primary' : ''"
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
            COPY OF DIPLOMA or DEGREE or TRANSCRIPTS
          </VExpansionPanelTitle>
          <VExpansionPanelText>
            <Diploma
              :name="docName('COPY OF DIPLOMA or DEGREE or TRANSCRIPTS')"
              @upload:doc="uploadFile2('COPY OF DIPLOMA or DEGREE or TRANSCRIPTS')"
            />
          </VExpansionPanelText>
        </VExpansionPanel>

      </VExpansionPanels>
    </VCardText>
  </VCard>
</template>