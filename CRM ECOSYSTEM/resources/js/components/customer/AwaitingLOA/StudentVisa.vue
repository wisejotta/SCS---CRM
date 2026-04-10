<script setup>
import photo from '@images/avatars/photo-specifications.png';
import { vModelRadio } from 'vue';

const props = defineProps({
  name: {
    type: String,
    required: true,
  },
})

const emit = defineEmits([
  'upload:doc',
])

const stidentVisa = ref({
  familyMember: undefined,
  familyMemberText: '',
  familyMemberPlace: '',

  usaResident: undefined,
})
</script>

<template>
  <VRow
    style="background-color: #eee;"
    class="ma-0 mt-4 mb-2"
  >
    <VCol
      cols="12"
      sm="6"
      style="background-color: #fff;"
    >
      <h3>What is it and why do I need it?</h3>
      <p>
        Just a few questions for our team to understand your individual situation as best they possibly can.
      </p>
    </VCol>

    <VCol
      cols="12"
      sm="6"
      class="mb-2"
    >
      <div class="mt-4">
        <h4>1. Do You Have A Family Member Who Is A Canadian Citizen OR Permanent Resident Of Canada? *</h4>
        <div class="">
          <VRadioGroup v-model="stidentVisa.familyMember">
            <VRadio
              label="Yes"
              :value="true"
            />
            <VRadio
              label="No"
              :value="false"
            />
          </VRadioGroup>

          If Yes, How Are They Related To You? *
          <VTextField
            class="mt-2 mb-2"
            v-model="stidentVisa.familyMemberText"
          />

          If Yes, Where In Canada Do They Reside? *
          <VTextField
            v-model="stidentVisa.familyMemberPlace"
          />
        </div>

        <VDivider class="mt-4 mb-4" />

        <h4>2. Are You A Permanent Resident Of The United States And Have Your Green Card Or Other Proof Of Permanent Residence? *</h4>
        <div class="">
          <VRadioGroup v-model="stidentVisa.usaResident">
            <VRadio
              label="Yes"
              :value="true"
            />
            <VRadio
              label="No"
              :value="false"
            />
          </VRadioGroup>
        </div>

        <VDivider class="mt-4 mb-4" />

        <h4>3. Have You Been Accepted To A Designated Learning Institution? *</h4>
        <div class="">
          <VRadioGroup v-model="stidentVisa.accepted">
            <VRadio
              label="Yes"
              :value="true"
            />
            <VRadio
              label="No"
              :value="false"
            />
          </VRadioGroup>
        </div>

        <VDivider class="mt-4 mb-4" />

        <h4>4. Are You A Spouse, Common-Law Partner Or Child Of Certain Skilled Worker Or Of Certain Full Time Student That Has Or Will Have Status In Canada? *</h4>
        <div class="">
          <VRadioGroup v-model="stidentVisa.spouse">
            <VRadio
              label="Yes"
              :value="true"
            />
            <VRadio
              label="No"
              :value="false"
            />
          </VRadioGroup>
        </div>

        <VDivider class="mt-4 mb-4" />

        <h4>5. Are You: A. A Recipient Of Commonwealth Scholarship; OR *</h4>
        <p>B. A Recipient Of A Full Bursary (Covering All Expenses) From The Canadian International Development Agency (CIDA), Including Francophonie Scholarships; OR C. A Participant In A Canadian Aid Program For Developing Countries?</p>
        <div class="">
          <VRadioGroup v-model="stidentVisa.commonwealthScholarship">
            <VRadio
              label="Yes"
              :value="true"
            />
            <VRadio
              label="No"
              :value="false"
            />
          </VRadioGroup>
        </div>

        <VDivider class="mt-4 mb-4" />

        <VBtn
          color="primary"
          @click="$emit('upload:doc', props.name)"
          :disabled="true"
        >
          Submit
        </VBtn>
      </div>
    </VCol>
  </VRow>
</template>