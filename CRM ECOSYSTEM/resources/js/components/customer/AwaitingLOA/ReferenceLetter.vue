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
        A letter from friends or family that state you intent to go back to your home country when you finish your studies.
      </p>
      <p>
        This just serves as further proof of ties to your home country.
      </p>
      <h3>How do I get this?</h3>
      <p>
        1. Ask your family and friends to write a letter.<br>
        2. You can then upload your documents here.<br>
      </p>
      <ul class="mb-4">
        <li>- The letter is addressed: "To whom it may concern"</li>
        <li>- State that they have known you in your home country</li>
        <li>- State that they recommend you as a reliable and committed person</li>
        <li>- Say that they know you are going to study in Canada and intend to return to your home country once your studies are completed because you have family responsibilities and/or future opportunities in your home country</li>
        <li>- The letter must be signed by the writer</li>
        <li>- The letter must be typed</li>
        <li>- Please also provide a copy of the passport OR copy of the id of the writer and upload it with the letter in the reference letter section.</li>
      </ul>
      <strong>*This is not mandatory but will be good to have</strong>
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