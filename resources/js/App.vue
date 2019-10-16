<script>
    import Report from './components/Report.vue';

    export default {
        components: {
            Report
        }
    }
</script>

<template>
    <div id="app">
        <h4>Report for finance operations</h4>
        <Report/>
    </div>
</template>

<style lang="scss">
    *, *::before, *::after {
        box-sizing: border-box;
    }

    #app {
        max-width: 500px;
        margin: 0 auto;
        line-height: 1.4;
    }
</style>
