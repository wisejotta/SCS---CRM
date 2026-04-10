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
      sm="7"
      style="background-color: #fff;"
    >
      <h3>What is it and why do you need this?</h3>
      <p>
        The IELTS (International English Language Testing System) is used to indicate your level of proficiency in the English language.
        <br><br>
        The Canadian Government places great importance on the fact that immigrants should be able to read, write and communicate adequately in English.
        <br><br>
        This ensures that candidates will be able to fit right in and not struggle to settle in.
      </p>
      <br><br>
      <h3>How do I get this?</h3>
      <ul>
        <li>1. Locate your nearest IELTS centre to take the test.</li>
        <li>2. Register on the country website of IELTS and register for a test at the optional dates.</li>
        <li>3. Make sure you select the Academic IELTS Exam, and not the General Training one.</li>
        <li>4. Study hard for the test (We offer an in depth preparation course that you can find below).</li>
        <li>5. Write the test when you are ready.</li>
        <li>6. Submit your result here.</li>
      </ul>
      <br>
      <video
        src="https://agent-dash-new.s3.amazonaws.com/img/old-source/IELTS+language+test+video.mp4#t=0.1"
        style="width: 100%;"
      ></video>
    </VCol>

    <VCol
      cols="12"
      sm="5"
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