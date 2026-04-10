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
        Official documents showing the family ties between the main applicant and the accompanying / supporting family members.
      </p>
      <h3>What serves as proof?</h3>
      <p>
        1. Birth certificate<br>
        2. Marriage certificate or acknowledgments from the state of common law (if they don't have this kind of proof they can use exempla at 3, 4, 5).<br>
        3. Proof that the main applicant and the spouse have been living together over a year (Contract with both names on it).<br>
        4. Proof of a joint bank account<br>
        5. If none of the above exists then the client needs to talk to the RCIC and think of a specific solution to prove relationships.<br>
      </p>
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