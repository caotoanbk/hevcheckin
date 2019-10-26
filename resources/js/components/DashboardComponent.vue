<template>
    <div class="container">
        <div class="row pt-5">
          <div class="col-lg-3 col-6 offset-3">
            <!-- small box -->
            <div class="small-box bg-info">
              <div class="inner">
                <h3>{{totalCards}}</h3>

                <p>Cards</p>
              </div>
              <div class="icon">
                <i class="fas fa-user"></i>
              </div>
              <a href="/card" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
              <div class="inner">
                <h3>{{totalEmployees}}</h3>

                <p>Employees</p>
              </div>
              <div class="icon">
                <i class="fas fa-id-card"></i>
              </div>
              <a href="/employee" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
        </div>
    </div>
</template>

<script>
    export default {
        data(){
            return {
                totalCards: '',
                totalEmployees: '',
            }
        },
        mounted() {
            console.log('Component mounted.');
        },
        created(){
            axios.get("api/card").then(({data}) => (this.totalCards = data.total));
            axios.get("api/employee").then(({data}) => (this.totalEmployees = data.total));
        }
    }
</script>
