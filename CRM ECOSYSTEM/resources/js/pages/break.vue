<script setup>

const timer = ref({})
const starting = ref(true)

let interval = null
axios
  .get('/api/breaks/available')
  .then((r) => {
    if(!r.data.type) {
      router.push('/home')
    } else {
      starting.value = false
      timer.value = r.data
      if(timer.value.seconds !== null) {
        startTimer()
      } else {
        timer.value.time = timer.value.type
      }
    }
  })
  .catch(e => { console.log(e) })

const router = useRouter()
const stop = () => {
  starting.value = true
  axios
    .get('/api/breaks/stop')
    .then((r) => {
      if(!interval) clearInterval(interval)
      router.push('/home')
    })
    .catch(e => {
      starting.value = false
      console.log(e)
    })
}

function startTimer() {
  timer.value.time = convertStoMs(timer.value.seconds)
  if(!interval) clearInterval(interval)
  interval = setInterval(() => {
    timer.value.seconds += 1
    timer.value.time = convertStoMs(timer.value.seconds)
  }, 1000)
}

function convertStoMs(seconds) {
  let minutes = Math.floor(seconds / 60);
  let extraSeconds = seconds % 60;
  minutes = minutes < 10 ? "0" + minutes : minutes;
  extraSeconds = extraSeconds< 10 ? "0" + extraSeconds : extraSeconds;
  return `${minutes}:${extraSeconds}`
}

const getBreakTimberColor = () => {
  let color = 'success'
  if(timer.value.type == 'cigarettes') {
    if(timer.value.seconds > 60 * 10) {
      color = 'error'
    } else if(timer.value.seconds > 60 * 8) {
      color = 'warning'
    }
  }

  if(timer.value.type == 'toilet') {
    if(timer.value.seconds > 60 * 5) {
      color = 'error'
    } else if(timer.value.seconds > 60 * 4) {
      color = 'warning'
    }
  }

  if(timer.value.type == 'lunch') {
    if(timer.value.seconds > 60 * 30) {
      color = 'error'
    } else if(timer.value.seconds > 60 * 25) {
      color = 'warning'
    }
  }
  return color
}

</script>

<template>
  <div class="misc-wrapper">
    <div class="text-center mb-12">
      <h4 class="text-h4 font-weight-medium mb-3">
        {{ timer.type ? timer.type.charAt(0).toUpperCase() + timer.type.slice(1) : '' }} Break!
      </h4>
      <p> You are on a {{ timer.type }} break.</p>

      <VBtn
        :color="getBreakTimberColor()"
        class="ms-n3 me-4"
        rounded
        :loading="starting"
        :disabled="starting"
      >
        {{ timer.type ? `${timer.type}: ${timer.time}` : '00:00' }}
        <VMenu activator="parent">
          <VList>
            <VListItem
              @click="stop"
            >
              <VListItemTitle>Stop</VListItemTitle>
            </VListItem>
          </VList>
        </VMenu>
      </VBtn>
    </div>
  </div>
</template>

<style lang="scss">
@use "@core-scss/template/pages/misc.scss";
</style>

<route lang="yaml">
meta:
  layout: blank
</route>
