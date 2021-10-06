<template>
    <div>
        <small>Today's activity:</small>
        <ul>
            <li v-for="item in activity">

            </li>
        </ul>
        <small>Day trades in last 5 days:</small>
        <ul>
            <li v-for="item in dayTrades">

            </li>
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
            //axios
        },
    },
}

</script>
