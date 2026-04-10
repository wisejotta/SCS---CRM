<script setup>
import navItems from '@/navigation/vertical';
import { useThemeConfig } from '@core/composable/useThemeConfig';

// Components
import Footer from '@/layouts/components/Footer.vue';
import UserProfile from '@/layouts/components/UserProfile.vue';
import Notifications from '@/components/Notifications.vue'

// @layouts plugin
import { VerticalNavLayout } from '@layouts';

const { appRouteTransition, isLessThanOverlayNavBreakpoint } = useThemeConfig()
const { width: windowWidth } = useWindowSize()

const breaks = ref([])
const starting = ref(false)

const router = useRouter()
axios
  .get('/api/breaks/available')
  .then((r) => {
    if(!r.data.type) {
      breaks.value = r.data
    } else {
      router.push('/break')
    }
  })
  .catch(e => { console.log(e) })
  
axios
  .get('/api/agents/reset-password')
  .then((r) => {
    if(r.data) {
      router.push('/reset-password')
    }
  })
  .catch(e => { console.log(e) })

const ratesLoading = ref(false)
const rate = ref(undefined)
const rateInput = ref('')
const rateEditing = ref(false)

const user = JSON.parse(localStorage.getItem('user'))
if(user.role == 'ADMIN') {
  axios
    .get('/api/rates')
    .then((r) => rate.value = r.data)
    .catch(e => console.log(e))
}

const editRate = () => {
  rateInput.value = rate.value.rate
  rateEditing.value = true
}

const saveRate = () => {
  if(!rateInput.value || rateInput.value == '') return
  const old = rate.value.rate
  rate.value.rate = rateInput.value
  rateEditing.value = false
  ratesLoading.value = true

  axios
    .post(`/api/rates/${rate.value.id}`, {
      rate: rateInput.value
    })
    .then((r) => { })
    .catch(e => {
      rate.value.rate = old
      rateEditing.value = true
      console.log(e)
    })
    .then(() => ratesLoading.value = false)
}

const colour = (index) => {
  const colours = ['primary', '', 'success', 'info', 'warning']
  return colours[index]
}

const icon = (index) => {
  const colours = ['smoking', 'bowl', 'man', 'video', 'chalkboard']
  return colours[index]
}

const start = (type) => {
  starting.value = true
  axios
    .get(`/api/breaks/start/${type}`)
    .then((r) => router.push('/break'))
    .catch(e => { console.log(e) })
    .then(starting.value = false)
}

</script>

<template>
  <VerticalNavLayout
    :nav-items="navItems"
  >
    <!-- 👉 navbar -->
    <template #navbar="{ toggleVerticalOverlayNavActive }">
      <div class="d-flex h-100 align-center">
        <VBtn
          v-if="isLessThanOverlayNavBreakpoint(windowWidth)"
          icon
          variant="text"
          color="default"
          class="ms-n3"
          size="small"
          @click="toggleVerticalOverlayNavActive(true)"
        >
          <VIcon
            icon="tabler-menu-2"
            size="24"
          />
        </VBtn>

        <VSpacer />

        <div
          v-if="rate"
        >
          <VBtn
            color="primary"
            class="ms-n3 me-4"
            :loading="ratesLoading"
            :disabled="ratesLoading"
            v-show="!rateEditing"
            @click="editRate"
            rounded
          >
            {{ `${rate.from} 👉 ${rate.to}${rate.rate}` }}
          </VBtn>

          <div
            class="ms-n3 me-6 pa-2"
            style="width: 180px; border: solid 1px #eee; border-radius: 5px;"
            v-show="rateEditing"
          >
            <VTextField
              v-model.trim="rateInput"
              type="text"
              :label="`${rate.from} to ${rate.to}`"
              color="primary"
              append-icon="tabler-device-floppy"
              append-inner-icon="tabler-x"
              @click:append-inner="rateEditing = !rateEditing"
              @click:append="saveRate"
            />
          </div>
        </div>

        <VBtn
          color="primary"
          class="ms-n3 me-4"
          rounded
          :loading="starting"
          :disabled="starting"
        >
          <VIcon
            icon="tabler-milkshake"
          />
          BREAK
          <VMenu activator="parent">
            <VList>
              <VListItem
                v-for="(_break, i) in breaks"
                @click="start(_break)"
              >
                <VListItemTitle>
                  <VChip
                    :color="colour(i)"
                  >
                    <VIcon
                      start
                      size="16"
                      :icon="`tabler-${icon(i)}`"
                    />
                    {{ _break }}
                  </VChip>
                  </VListItemTitle>
              </VListItem>
            </VList>
          </VMenu>
        </VBtn>

        <Notifications class="me-8" :notifications="[]" />

        <UserProfile />
      </div>
    </template>

    <!-- 👉 Pages -->
    <RouterView
      v-slot="{ Component }"
    >
      <Transition
        :name="appRouteTransition"
        mode="out-in"
      >
        <Component :is="Component" />
      </Transition>
    </RouterView>

    <!-- 👉 Footer -->
    <template #footer>
      <Footer />
    </template>

    <!-- 👉 Customizer -->
    <!-- <TheCustomizer /> -->
  </VerticalNavLayout>
</template>