<template>
    <button 
        class="btn btn-default"
        v-bind:class="{ 'btn-success': followed }"
        v-on:click="follow"
        v-text="text"
    ></button>
</template>

<script>
    export default {
        props:['question', 'user'],
        mounted() {
            this.$http.post('/api/question/follower',{'question':this.question})
            .then(response=>{
                this.followed = response.data.followed
            })
        },
        data() {
            return {
                followed: false
            }
        },
        computed: {
            text() {
                return this.followed ? '已关注':'关注该问题'
            }
        },
        methods: {
            follow() {
                this.$http.post('/api/question/follow', {'question': this.question})
                .then(response=>{
                    this.followed = response.data.followed
                })
            }
        }
    }
</script>
