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
        This is a description of what you plan to study in Canada, and why you would like to study in Canada. You also need to mention why you chose the specific institution and program you enrolled for.
      </p>
      <p>
        Make sure you place strong emphasis on the opportunities that it will provide you in your home country.
      </p>

      <h3>How do I do this?</h3>
      <p>Please write your plan in the block provided here.</p>
      <p>It will generate a document.</p>
      <p>If you are unhappy with it, feel free to delete and write a new one.</p>
      <strong>*Once you have provided your study plan - our academic advisor will review and ensure that the letter is clear and written in good English. The advisor might require a call with you. But we will let you know.</strong>
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