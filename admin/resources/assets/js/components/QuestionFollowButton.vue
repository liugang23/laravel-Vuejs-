<template>
    <button 
        class="btn btn-default"
        v-text="text"
    ></button>
</template>

<script>
    export default {
        mounted() {
            this.$http.post('api/question/follower',{})
            .then(response=>{
                console.log(response.data);
            })
        },
        data() {
            return {
                followed: false;
            }
        },
        computed: {
            text() {
                return this.followed ? '已关注' ：'关注该问题'
            }
        }
    }
</script>
