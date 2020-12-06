<template>
    <div class="container">

        <div class="row">
            <div class="col-12">
                <form @submit.prevent="editmode ? updateTask() : createTask()">
      
                    <div class="form-group">

                        <h5 v-if="!editmode" id="exampleModalLabel">Create New Todo</h5>
                        <h5 v-if="editmode" id="exampleModalLabel">Update Todo</h5>
        
                        <textarea @keydown.enter.exact.prevent="createOrUpdate" v-model="form.content" type="text" 
                            name="content" placeholder="Enter Todo" class="form-control" 
                            :class="{ 'is-invalid': form.errors.has('content') }" rows="2">
                        </textarea>
                        <has-error :form="form" field="content"></has-error>
                    </div>

                    <button v-if="!editmode" type="submit" class="btn btn-primary">Create</button>
                    <button v-if="editmode" type="submit" class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-secondary" @click="clearForm()">Cancel or Clear Form</button>
                </form>
                
            </div>
        </div>

        <hr>


        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Todo List</h3>

                
              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                  <thead>
                    <tr>
                      <th># id</th>
                      <th>Content</th>
                      <th>Status( done / not done )</th>
                      <th>Date</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    
                    <tr v-for="(task, index) in tasks" :index="index" :key="task.id">
                      <td>{{task.id}}</td>
                      <td>{{task.content}}</td>
                      <td>
                          <toggle-button @change="changeStatus(task)" :value="task.status" v-model="task.status" 
                        color="#3490dc"/>
                      </td>
                      <td>{{task.created_at}}</td>
                      <td>
                          <a href="javascript:void(0)" @click="editTask(task)">
                            update
                          </a>
                          |
                          <a href="javascript:void(0)" style="color:red" @click="deleteTask(task)">
                            delete
                          </a>
                          
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
              
            </div>
            <!-- /.card -->
          </div>
        </div>

        <!-- if tasks is empty this will display -->
        <div v-if="!tasks.length" class="alert alert-warning" role="alert">
        No records found.
        </div>
        <div v-else>
            <hr>
            <button type="button" class="btn btn-danger btn-lg btn-block" @click="clearList">Clear List</button>
        </div>
        
        
    </div>
</template>

<script>
    export default {
        data(){
            return{
                editmode: false,
                tasks: {},
                form: new Form({
                    id: '',
                    content: '',
                    user_id: '',
                    status: '',
                })

            }

        },

        methods:{
            // function to clear all list
            clearList(){
                swal.fire({
                    title: 'Are you sure to delete all records?',
                    text: "Deleted items can not be recovered!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete!'
                  }).then((result) => {
                    
                    if(result.value){

                        let user_id = $('#person').data('id')
                        axios.get('api/deleteall', {
                            params: {
                                user_id: user_id,
                            },
                        })
                        .then(function(response){

                        if(response.data.status === 'success')
                        {
                          Reload.$emit('ReloadContent')
                          swal.fire(
                            'Deleted!',
                            'Task has been deleted successfully!',
                            'success'
                          )

                        }

                      })
                      .catch(()=>{

                          swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Something went wrong!',
                          })

                      });
                      
                    }

                  })

            },
            // function changes status of task(done / not done)
            changeStatus(task){
                this.form.fill(task)
                this.form.post('api/changestatus')
                .then(function(response){

                    if(response.data.status === 'success')
                    {
                      Reload.$emit('ReloadContent');
                      swal.fire(
                        'Updated!',
                        'Task status successfully has been changed!',
                        'success'
                      )
                    }
                })
                .catch(()=>{

                    // if failed
                    swal.fire({
                      icon: 'error',
                      title: 'Oops...',
                      text: 'Something went wrong!',
                    })
                })

            },

            // when press enter inside form, this function checks current mode (editmode or update mode) and edits/updates
            createOrUpdate(){

                if(this.editmode){
                    this.updateTask()
                } else {
                    this.createTask()
                }
            },

            // this function for clear the form
            clearForm(){
                this.form.clear();
                this.form.reset();
                this.editmode = false;
            },
            // function to delete selected task
            deleteTask(task){
                swal.fire({
                    title: 'Are you sure to delete?',
                    text: "Deleted items can not be recovered!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete!'
                  }).then((result) => {
                    
                    if(result.value){

                      this.form.fill(task)
                      // send request to the server
                      this.form.delete('api/tasks/'+this.form.id)
                      .then(function(response){

                        if(response.data.status === 'success')
                        {
                          Reload.$emit('ReloadContent')
                          swal.fire(
                            'Deleted!',
                            'Task has been deleted successfully!',
                            'success'
                          )

                        }

                      })
                      .catch(()=>{

                          swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Something went wrong!',
                          })

                      });
                      
                    }

                  })

            },

            // if edit button pressed, selected value will be filled inside the form for edit
            editTask(task){
                this.editmode = true
                this.form.fill(task)
            },

            // function to update task
            updateTask(){
                this.form.put('api/tasks/'+this.form.id)
                .then(function(response){

                    if(response.data.status === 'success')
                    {
                      Reload.$emit('ReloadContent');
                      swal.fire(
                        'Updated!',
                        'Task has been updated successfully!',
                        'success'
                      )
                    }
                })
                .catch(()=>{

                    // if failed
                    swal.fire({
                      icon: 'error',
                      title: 'Oops...',
                      text: 'Something went wrong!',
                    })
                })
                
            },

            // function to create task
            createTask(){
                this.form.user_id = $('#person').data('id');
                this.form.post('api/tasks')
                .then(function(response){

                    if(response.data.status === 'success')
                    {
                      Reload.$emit('ReloadContent')
                      console.log("Task Saved Successfully!!! ")

                      swal.fire({
                        icon: 'success',
                        title: 'New task has been added !!!',
                        showConfirmButton: false,
                        timer: 2000
                        })

                    }
                    
                })
                .catch(()=>{

                    // if failed
                    console.log("Failed ")
                    swal.fire({
                      icon: 'error',
                      title: 'Oops...',
                      text: 'Something went wrong!',
                    })
                })

            },

            // function to load tasks
            loadTasks(){

                    let user_id = $('#person').data('id')
                    axios.get('api/tasks', {
                        params: {
                            user_id: user_id,
                        },
                    })
                    .then(({ data }) => (
                        this.tasks = data.tasks
                    ))
                    .catch(()=>{

                        // if failed
                        console.log('Something is wrong!!!');
                        // swal.fire({
                        //   icon: 'error',
                        //   title: 'Oops...',
                        //   text: 'Something went wrong!',
                        // })
                    });
            },

            
        },

        mounted() {
            this.loadTasks()

            // this reloads all contents after create/update/delete some todos
            Reload.$on('ReloadContent', () => {
                this.editmode = false;
                this.form.reset();
                this.loadTasks();
            });
        }
    }
</script>
