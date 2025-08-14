<template>

</template>
<script>
import axios from "axios";
axios.defaults.baseURL = "/api";
axios.defaults.headers.common["Accept"] = "application/json";
export default {
    data() {
        return {
            form: {
                event_name: "",
                event_date: "",
                event_time: "",
                event_location: "",
                event_description: ""
            },
            loading: false,
            message: "",
        };
    },
    methods: {
        async submitForm() {

        }
    }
};
</script>
