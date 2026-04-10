import axios from '@axios'

export const useCalendarStore = defineStore('calendar', {
  state: () => ({
    lead: null,
    isLeadDialogVisible: false,
  }),
  actions: {
    async fetchEvents(userId) {
      return !userId
        ? axios.get('/api/leads/callbacks/events')
        : axios.get(`/api/leads/callbacks/events/${userId}`)
    },
  },
})
