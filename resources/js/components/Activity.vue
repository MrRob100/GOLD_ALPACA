<template>
    <div>
        <small>Today's activity:</small>
        <ul>
            <li v-for="(item, date) in dayTrades">{{ date }}: {{ item }}</li>

        </ul>
        <small>Day trades in last 5 days:</small>
        <ul class="activity-list">
            <li v-for="(item, date) in activity">{{ date }}: {{ item }}</li>
        </ul>
    </div>
</template>

<script>
export default {
    data: function() {
        return {
            activity: [],
            dayTrades: [],
        };
    },

    mounted() {
        this.getData();
    },

    methods: {
        getData: function() {
            axios.get('/latest_activity').then(response => {
                this.activity = response.data.last_5_days;
                this.dayTrades = response.data.today;
            });
        },
    },
}

</script>
<style>
    .activity-list {
        height: 100px;
        overflow-y: scroll;
    }
</style>
