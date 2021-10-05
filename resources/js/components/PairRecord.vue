<template>
    <div class="row">
        <div class="col-10 m-auto">
            <div class="row mb-2">
                <div class="pr-2 col-3">
                    <select class="form-control" id="month" name="month" v-model="month" @change="getData(s1, s2, month)">
                        <option v-for="month in data.months" :value="month.value">{{ month.name }}</option>
                    </select>
                </div>
                <button @click="getData(s1, s2)" class="btn btn-primary col-2 mr-2">All</button>
                <button @click="getData(s1, s2, month)" class="btn btn-primary col-2">Per Month</button>
            </div>
            <div id="performance_container">
            </div>
        </div>
        <div class="col-10 m-auto">
            <table v-if="data.length != 0" class="table table-bordered table-striped table-hover table-vcenter">
                <thead>
                <tr>
                    <th></th>
                    <th class="text-center" colspan="5">Real</th>
                    <th class="text-center" colspan="2"></th>
                    <th class="text-center">If Holding (from time)</th>
                    <th class="text-center">If $</th>
                </tr>
                <tr>
                    <th></th>
                    <th class="text-center" colspan="2">Balance {{ s1 }}</th>
                    <th class="text-center" colspan="2">Balance {{ s2 }}</th>
                    <th class="text-center">Σ</th>
                    <th class="text-center" colspan="2">Δ</th>
                    <th class="text-center">Σ</th>
                    <th class="text-center">Σ</th>
                </tr>
                <tr>
                    <th class="text-center">Date</th>
                    <th class="text-center">{{ s1 }}</th>
                    <th class="text-center">$</th>
                    <th class="text-center">{{ s2 }}</th>
                    <th class="text-center">$</th>
                    <th class="text-center">$</th>
                    <th class="text-center">Δh</th>
                    <th class="text-center">Δi</th>
                    <th class="text-center">$</th>
                    <th class="text-center">$</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="item in data.records">
                    <td>{{ formatDate(item.created_at) }}</td>
                    <td>{{ parseFloat(item.balance_s1).toFixed(2) }}</td>
                    <td>{{ parseFloat(item.balance_s1_usd).toFixed(2) }}</td>
                    <td>{{ parseFloat(item.balance_s2).toFixed(2) }}</td>
                    <td>{{ parseFloat(item.balance_s2_usd).toFixed(2) }}</td>
                    <td class="bg-info text-light">{{ (item.balance_total_usd).toFixed(2) }}</td>

                    <td class="text-light" :class="item.delta_worth_and_worth_if_holding_usd > 0 ? 'bg-success' : 'bg-danger'"
                    >{{ item.delta_worth_and_worth_if_holding_usd.toFixed(2) }}</td>

                    <td :class="item.profit_usd > 0 ? 'bg-success' : 'bg-danger'"
                    >{{ item.profit_usd.toFixed(2) }}</td>
                    <td class="bg-dark text-light">{{ item.worth_if_holding.toFixed(2) }}</td>
                    <td class="bg-secondary text-light">{{ item.total_input_usd.toFixed(2) }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr v-if="bals1 !== 0 && bals1_usd !== 0 && bals2 !== 0 && bals2_usd !== 0 && showLatestRow">
                    <td>{{ formatDate(new Date()) }}</td>
                    <td>{{ bals1.toFixed(2) }}</td>
                    <td>{{ bals1_usd.toFixed(2) }}</td>
                    <td>{{ bals2.toFixed(2) }}</td>
                    <td>{{ bals2_usd.toFixed(2) }}</td>
                    <td class="bg-info text-light">{{ totalBalNow }}</td>
                    <td class="text-light" :class="(totalBalNow - valueIfHoldingNow) > 0 ? 'bg-success' : 'bg-danger'">
                        {{ (totalBalNow - valueIfHoldingNow).toFixed(2) }}
                    </td>
                    <td :class="(totalBalNow - totalInput) > 0 ? 'bg-success' : 'bg-danger'">
                        {{ (totalBalNow - totalInput).toFixed(2) }}
                    </td>
                    <td class="bg-dark text-light">{{ valueIfHoldingNow }}</td>
                    <td class="bg-secondary text-light">{{ totalInput }}</td>
                </tr>
                </tbody>
            </table>
            <br>
            <button v-if="pricec1" @click="getBalances" class="btn btn-primary">Get Latest</button>
        </div>
    </div>
</template>

<script>
import Chart from 'chart.js';

export default {
    props: [
        "value",
        "push-lasts",
        "balance-route",
        "latest-data-route",
    ],
    data: function() {
        return {
            month: null,
            data: [],
            pricec1: null,
            pricec2: null,
            s1: null,
            s2: null,
            bals1: 0,
            bals1_usd: 0,
            bals2: 0,
            bals2_usd: 0,
            pricec1Now: null,
            pricec2Now: null,
            latest: false,
            showLatestRow: false,
            latestInput: {
                s1: {
                    s1: null,
                    usd: null,
                },
                s2: {
                    s2: null,
                    usd: null,
                },
            },
            graphData: {
                type: "bar",
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: "Pair value if holding",
                            type: "line",
                            data: [],
                            backgroundColor: "rgba(54,73,93,.5)",
                            borderColor: "#36495d",
                            borderWidth: 3
                        },
                        {
                            label: "Pair Value",
                            type: "line",
                            data: [],
                            backgroundColor: "rgba(71, 183,132,.5)",
                            borderColor: "#47b784",
                            borderWidth: 3
                        },
                        {
                            label: "Input",
                            type: "bar",
                            data: [],
                            backgroundColor: "rgba(71,127,183,0.5)",
                            borderColor: "#4779b7",
                            borderWidth: 3
                        }
                    ]
                }
            }
        };
    },

    methods: {
        formatDate: function(date) {
            var obj = new Date(date);
            return obj.toLocaleDateString();
        },
        getData: function(s1, s2, month = null) {
            this.s1 = s1;
            this.s2 = s2;
            axios.get('/get_pair_data', {
                params: {
                    s1,
                    s2,
                    month,
                }
            }).then(response => {
                this.data = response.data;
                this.month = response.data.current_month;
                this.formatChartData(response.data);
            });
        },

        formatChartData: function(data) {
            let labels = [];

            Object.values(data.records).forEach((item) => {
                labels.push(item.created_at.substring(0, 10));
            });

            this.graphData.data.labels = labels;
            this.graphData.data.datasets[0].data = data.worth_if_holding;
            this.graphData.data.datasets[1].data = data.balance_total_usd;
            this.graphData.data.datasets[2].data = data.cumulative_inputs;

            this.newChart();
        },
        newChart: function() {
            let oldCanvasContainer =  document.getElementById('performance_container');

            if (typeof(oldCanvasContainer) != 'undefined' && oldCanvasContainer != null) {
                oldCanvasContainer.innerHTML = '';
            }

            let canvasContainer = document.getElementById('performance_container');
            let canvas = document.createElement('canvas');
            canvasContainer.appendChild(canvas);

            new Chart(canvas, this.graphData);
        },
        pushLatestToChart: function() {
            let today = new Date();

            if (this.latest) {
                let offset = this.graphData.data.datasets[0].data.length - 1;

                this.graphData.data.labels[offset] = today.toISOString().split('T')[0];
                this.graphData.data.datasets[0].data[offset] = this.valueIfHoldingNow;
                this.graphData.data.datasets[1].data[offset] = this.totalBalNow;
                this.graphData.data.datasets[2].data[offset] = this.totalInput;
            } else {
                this.graphData.data.labels.push(today.toISOString().split('T')[0]);
                this.graphData.data.datasets[0].data.push(this.valueIfHoldingNow);
                this.graphData.data.datasets[1].data.push(this.totalBalNow);
                this.graphData.data.datasets[2].data.push(this.totalInput);
            }


            this.newChart();
            this.latest = true;
        },
        getBalances: function() {
            let _this = this;
            _this.showLatestRow = true;

            axios.get(this.latestDataRoute, {
                params: {
                    s1: this.s1,
                    s2: this.s2,
                }
            }).then(function (response) {
                _this.bals1 = response.data.s1.qty;
                _this.bals1_usd = response.data.s1.value;
                _this.bals2 = response.data.s2.qty;
                _this.bals2_usd = response.data.s2.value;
                _this.pricec1Now = response.data.s1.price;
                _this.pricec2Now = response.data.s2.price;
                _this.latestInput.s1.usd = response.data.s1.latest_input.amount_usd;
                _this.latestInput.s1.s1 = response.data.s1.latest_input.amount;
                _this.latestInput.s2.usd = response.data.s1.latest_input.amount_usd;
                _this.latestInput.s2.s2 = response.data.s1.latest_input.amount;
                _this.pushLatestToChart();
            });
        },
    },
    computed: {
        totalBalNow: function() {
            return (this.bals1_usd + this.bals2_usd).toFixed(2);
        },
        latestRecord: function() {
            return Object.values(this.data.records)[Object.keys(this.data.records).length - 1];
        },
        latestInputTotal: function() {
            return this.latestInput.s1.usd + this.latestInput.s2.usd;
        },
        totalInput: function() {
            return (this.latestRecord.total_input_usd + this.latestInputTotal).toFixed(2);
        },
        valueIfHoldingNow: function() {
            return (((this.latestRecord.input_s1 + this.latestInput.s1.s1) * this.pricec1Now) + ((this.latestRecord.input_s2 + this.latestInput.s2.s2) * this.pricec2Now)).toFixed(2);
        }
    },
    watch: {
        value: function(val) {
            this.showNewRecord = false;
            if (val.length == 2) {
                this.getData(val[0].name, val[1].name);
            }
        },
        pushLasts: function(val) {
            this.pricec1 = val[0].s1;
            this.pricec2 = val[1].s2;
        },
        s1: function() {
            this.showLatestRow = false;
            this.latest = false;
        },
        s2: function() {
            this.showLatestRow = false;
            this.latest = false;
        }
    }
}

</script>
