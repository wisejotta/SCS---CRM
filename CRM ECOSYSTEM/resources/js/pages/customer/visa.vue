<script setup>
import UserProfile from './UserProfile.vue'
import AwaitingLOA from '@/components/customer/AwaitingLOA.vue'
import FileOpening from '@/components/customer/FileOpening.vue'
import LOAReceived from '@/components/customer/LOAReceived.vue'
import PreSubmission from '@/components/customer/PreSubmission.vue'
import canada from '@images/avatars/canadian-flag.png'

const loading = ref(true)
const user = JSON.parse(localStorage.getItem('user'))
const customer = ref({
  title: `${user.name} ${user.surname}`,
})

const viewResults = link => {
  window.open(link, "_blank")
}

const router = useRouter()
const back = () => {
  router.back()
}

const steps = 2
const e1 = ref(1)
const refresh = () => {
  loading.value = true
  axios.get('/api/customers/customer').then((r) => {
    loading.value = false
    customer.value = r.data
    e1.value = r.data.status == 5 ? 1 : 2
  })
}

refresh()

const name = (n) => ['File Opening', 'Upgrade'][n - 1]

</script>

<template>  
  <div class="pa-4">
    <VCard class="mb-4">
      <div class="demo-space-x pa-3">
        <VBtn size="38" rounded @click="back">
          <VIcon
            icon="tabler-chevron-left"
            size="22"
          />
        </VBtn>
        <h5 class="text-h5">
          {{ customer.title }}
        </h5>
        <VSpacer />
        <UserProfile />
      </div>
    </VCard>

    <VProgressLinear
      :active="loading"
      :indeterminate="loading"
      v-show="loading"
      absolute
      bottom
      color="deep-purple accent-4"
      class="mb-4"
    />

    <VCard
      :title="customer.product"
      class="mb-4"
      v-show="!loading"
    >
      <template #append>
        <VBtn
          color="primary"
          :disabled="!customer.results"
        >
          <VIcon
            size="22"
            icon="tabler-eye"
            class="me-2"
          />
          View Results
          <VMenu activator="parent">
            <VList>
              <VListItem v-for="item in customer.results" @click="viewResults(item.file)">
                <VListItemTitle>{{ item.name }}</VListItemTitle>
              </VListItem>
            </VList>
          </VMenu>
        </VBtn>
      </template>

      <VCardText>
        <div class="demo-space-x">
          <VAvatar :image="canada" color="primary"/>
          <h3>Canada</h3>
        </div>
      </VCardText>
    </VCard>

    <v-stepper
      v-model="e1"
      class="mb-4"
    >
      <template v-slot:default="{ prev, next }">
        <v-stepper-header>
          <template v-for="n in steps" :key="`${n}-step`">
            <v-stepper-item
              :complete="e1 > n"
              :step="`Step {{ n }}`"
              :value="n"
              :title="name(n)"
              color="error"
            ></v-stepper-item>

            <v-divider
              v-if="n !== steps"
              :key="n"
            ></v-divider>
          </template>
        </v-stepper-header>

        <v-stepper-window>
          <v-stepper-window-item
            v-for="n in steps"
            :key="`${n}-content`"
            :value="n"
          >
            <div v-if="n == 1">
              <FileOpening
                v-if="!loading" @refresh="refresh"
                :documents="customer.docs"
              />
            </div>
            <div v-else>
              <AwaitingLOA
                v-if="!loading"
                :loa="customer.loa"
                :documents="customer.docs"
                @refresh="refresh"
                class="mb-4"
              />

              <LOAReceived
                v-if="!loading"
                v-show="!loading"
                :documents="customer.docs"
                class="mb-4"
              />

              <PreSubmission
                v-if="!loading"
                v-show="!loading"
                :documents="customer.docs"
                class="mb-4"
              />
            </div>
            
          </v-stepper-window-item>
        </v-stepper-window>
      </template>
    </v-stepper>
  </div>
</template>

<style lang="scss">
@use "@core-scss/template/pages/misc.scss";
</style>

<route lang="yaml">
meta:
  layout: blank
</route>
