<script setup>

const props = defineProps({
  name: {
    type: String,
    required: true,
  },
})

const emit = defineEmits([
  'upload:doc',
])
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
        Any and all proof that you can provide that will convince the Canadian Government that you plan on returning home once your studies are done.
      </p>
      <p>
        What serves as proof?
      </p>

      <h4>Financial capacity</h4>
      <p>
        Please see examples of documents which can be submitted to support your application:<br>
        1. The contract signed when you purchased your home.<br>
        2. Mortgage payment or property tax receipts.<br>
        3. Homeowners insurance in your name.<br>
        4. Investment papers.<br>
        5. Cars, motorbikes, etc.<br>
      </p>

      <h4>Professional capacity</h4>
      <p>
        Please see examples of documents which can be submitted to support your application:<br>
        1. Statement from employer specifying your job duties, length of employment, and salary.<br>
        2. Letter written by employer giving you permission to take paid time off.<br>
        3. Pay stubs, or other documentation proving your income.<br>
        4. Statements showing revenue, if you run a business.<br>
        5. Financial Assets/Real Estate.<br>
        6. Property deeds.<br>
        7. The contract signed when you purchased your home.<br>
        8. Mortgage payment or property tax receipts.<br>
        9. Homeowners insurance in your name.<br>
        10. Investment papers.<br>
        11. Documentation used initially to establish your business — such as articles of incorporation.<br>
      </p>

      <h4>Personal Capacity</h4>
      <p>
        Please see examples of documents which can be submitted to support your application:<br>
        1. Documented prospects or plans for when you arrive back home - this can be in the form of a word document, signed by you, the main applicant.<br>
        2. Any documentation showing that you're integral to the organization.<br>
        3. Letters from members of the organization stating your importance to the community.<br>
        4. Official documents proving your current status as a political figure in the community.<br>
        5. Student ID, if currently enrolled.<br>
        6. Photos showing a sincere connection to family and friends.<br>
        7. Signed letters from family and friends indicating a close bond.<br>
        8. Please Upload your supporting documents<br>
      </p>
      <strong>*Once you have provided all the proof you can. Our team will put it into a summary that is ready to submit along with your application.</strong>
    </VCol>

    <VCol
      cols="12"
      sm="6"
      class="mb-2 text-center"
    >
      <div class="mt-4">
        <i> {{ props.name || 'Your Documents' }}</i>
        <br>
        <br>
        <VBtn
          color="primary"
          @click="$emit('upload:doc', props.name)"
        >
          <VIcon
            size="22"
            icon="tabler-cloud-upload"
          />
          {{ props.name ? ' Replace' : ' Upload' }}
        </VBtn>
      </div>
    </VCol>
  </VRow>
</template>