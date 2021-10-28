<template>
    <CRow>
        <CCol col="12">
            <CCard>
                <CCardHeader>
                    <h4>Order {{order.token}}</h4>
                </CCardHeader>
                <CCardBody>
                    <CCard>
                        <CCardHeader @click="detailCollapse = !detailCollapse">
                            <strong>Transaction Details</strong>
                        </CCardHeader>
                        <!-- <CCardBody> -->
                            <CCollapse :show="detailCollapse" class="mt-2">
                                <CCardBody>
                                <table class="table">
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-right" style="width:125px">Qty</th>
                                        <th class="text-right">Price</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                    <tr v-for="(item, $index) in order_items" :key="$index">
                                        <td>{{ item.product.name }}</td>
                                        <td class="text-right">{{ item.quantity }}</td>
                                        <td class="text-right">{{ item.product.price }}</td>
                                        <td class="text-right">{{ item.total_price }}</td>
                                    </tr>
                                    <tr v-if="order.promotion_id">
                                        <th colspan="3" class="text-right" >Sub Total</th>
                                        <th class="text-right">{{ order.total_price }}</th>
                                    </tr>
                                    <tr v-if="order.promotion_id">
                                        <th colspan="3" class="text-right" >Discount</th>
                                        <th class="text-right">{{ order.discount }}</th>
                                    </tr>
                                    <tr>
                                        <th colspan="3" class="text-right" >Total</th>
                                        <th class="text-right">{{ order.final_price }}</th>
                                    </tr>
                                </table>
                                </CCardBody>
                            </CCollapse>
                        <!-- </CCardBody> -->
                    </CCard>
                </CCardBody>
                <CCardBody v-if="order.is_saved == 1 && order.is_paid == 0">
                    <CRow>
                        <CCol>

                            <div class="text-center" v-if="order.payment_type == 0">
                                <h5>Waiting for payment... <br>
                                Total bill IDR {{ order.final_price }}<br>
                                Please complete your transaction at the cashier.</h5>
                            </div>

                            <div class="text-center" v-if="order.payment_type !== 0">
                                <div v-if="order.payment_type == 3">
                                    <CRow class="pb-2 text-center">
                                        <CCol>
                                            <h5>Waiting for payment...<br>
                                            Total bill IDR {{ order.final_price }}<br></h5>
                                            <img :src="'/img/bank/bca.png'" style="max-width:240px;"><br>
                                            <h6>803123456 a/n Segal</h6>
                                        </CCol>
                                    </CRow>
                                </div>
                                <div v-if="order.payment_type == 1">
                                    <CRow class="pb-2 text-center">
                                        <CCol>
                                            <h5>Waiting for payment...<br>
                                            Total bill IDR {{ order.final_price }}<br></h5>
                                            <img :src="'/img/bank/qr-code.png'" style="max-width:240px;"><br>
                                            <h6>(Ini detail QRIS nya)</h6>
                                        </CCol>
                                    </CRow>
                                </div>
                                <CRow>
                                    <CCol>
                                        <label for="berkas">Confirm Payment</label>
                                        <CInputFile
                                            custom
                                            id="berkas"
                                            @change="generateBase64()"
                                        />
                                        <input type="hidden" id="img">
                                        <img class="text-center" id="thumbnail">
                                    </CCol>
                                </CRow>
                                <CRow>
                                    <CCol>
                                        <CAlert
                                            :show.sync="dismissCountDown"
                                            color="primary"
                                            fade
                                        >
                                            ({{dismissCountDown}}) {{ message }}
                                        </CAlert>
                                    </CCol>
                                </CRow>
                                <CRow>
                                    <CCol>
                                        <CButton class="float-right" color="success" @click="confirmPayment()">Confirm</CButton>
                                    </CCol>
                                </CRow>
                            </div>

                        </CCol>
                    </CRow>
                </CCardBody>

                <CCardBody v-if="order.is_saved == 1 && order.is_paid == 1 && order.status == 1">
                    <div class="text-center">
                        <h5>Please wait, payment is being verified <br>
                        Total bill IDR {{ order.final_price }}</h5><br>
                        <CButton class="text-center" color="info" @click="refreshPayment()">Refresh</CButton>
                    </div>
                </CCardBody>

                <CCardBody v-if="order.status == 2">
                    <div class="text-center">
                        <h5>Your order has been finished <br>
                        Total bill IDR {{ order.final_price }}</h5><br>
                        <CButton class="text-center" color="info" @click="reOrder()">Order Again</CButton>
                    </div>
                </CCardBody>
            </CCard>
        </CCol>
    </CRow>
</template>

<script>
import axios from 'axios'
export default {
    name: 'CheckoutOrder',
    data () {
        return {
            order: {},
            order_items: [],
            detailCollapse: false,
            message: '',
            dismissSecs: 7,
            dismissCountDown: 0,
            showDismissibleAlert: false,
        }
    },
    methods: {
        getOrderDetail() {
            let self = this
            axios.get(this.$apiAdress + '/api/order/getOrderDetail?token=' + localStorage.getItem("api_token"), {
                params: {
                    uuid: this.$route.params.uuid,
                },
            }).then(({ data }) => {
                self.order = data
            });
        },
        getOrderItems() {
            let self = this
                axios.get(this.$apiAdress + '/api/order/orderItems?token=' + localStorage.getItem("api_token"), {
                    params: {
                        uuid: this.$route.params.uuid
                    },
                }).then(({ data }) => {
                        self.order_items = data.order_items
                });
        },
        generateBase64() {
            var fileReader = new FileReader();
            var filterType = /^(?:image\/bmp|image\/cis\-cod|image\/gif|image\/ief|image\/jpeg|image\/jpg|image\/JPG|image\/JPEG|image\/pipeg|image\/png|image\/PNG|image\/svg\+xml|image\/tiff|image\/x\-cmu\-raster|image\/x\-cmx|image\/x\-icon|image\/x\-portable\-anymap|image\/x\-portable\-bitmap|image\/x\-portable\-graymap|image\/x\-portable\-pixmap|image\/x\-rgb|image\/x\-xbitmap|image\/x\-xpixmap|image\/x\-xwindowdump)$/i;
            var uploadImage = document.getElementById("berkas");
            if (uploadImage.files.length === 0) {
                return;
            }
            var uploadFile = document.getElementById("berkas").files[0];
            if (!filterType.test(uploadFile.type)) {
                alert("Please select a valid image.");
                return;
            }
            fileReader.readAsDataURL(uploadFile);
            fileReader.addEventListener('load', function (){
                var image = new Image();
                image.onload=function(){
                    var max_h = 1000;
                    var max_w = 1000;
                    var thumb = 300;
                    var w = image.width;
                    var h = image.height;
                    var t_w = image.width;
                    var t_h = image.height;
                    if(w > max_w){
                        h*=max_w/w;
                        w=max_w;
                    }
                    if(t_w > thumb){
                        t_h*=thumb/t_w;
                        t_w=thumb;
                    }
                    if(h > max_h){
                        w*=max_h/h;
                        h=max_h;
                    }
                    if(t_h > thumb){
                        t_w*=thumb/t_h;
                        t_h=thumb;
                    }
                    var canvas = document.createElement('canvas');
                    canvas.width = w;
                    canvas.height = h;
                    canvas.getContext('2d').drawImage(image, 0, 0, w, h);
                    var t_canvas = document.createElement('canvas');
                    t_canvas.width = t_w;
                    t_canvas.height = t_h;
                    t_canvas.getContext('2d').drawImage(image, 0, 0, t_w, t_h);
                    var dataURL = canvas.toDataURL("image/png");
                    var t_dataURL = t_canvas.toDataURL("image/png");
                    document.getElementById("thumbnail").src = t_dataURL;
                    document.getElementById("img").value = dataURL;
                }
                image.src=event.target.result;
            });
        },
        confirmPayment() {
            let self = this;
            self.order.payimage = document.getElementById('img').value
            axios.post(  this.$apiAdress + '/api/order/confirmPayment?token=' + localStorage.getItem("api_token"),
                {
                    img: self.order.payimage,
                    uuid: this.$route.params.uuid,
                }
            )
            .then(function (response) {
                self.$router.go()
            }).catch(function (error) {
                if(error.response.data.message == 'The given data was invalid.'){
                    self.message = '';
                    for (let key in error.response.data.errors) {
                        if (error.response.data.errors.hasOwnProperty(key)) {
                            self.message += error.response.data.errors[key][0] + '  ';
                        }
                    }
                    self.showAlert();
                }else{
                    console.log(error);
                    self.$router.push({ path: 'login' });
                }
            });
        },
        countDownChanged (dismissCountDown) {
            this.dismissCountDown = dismissCountDown
        },
        showAlert () {
            this.dismissCountDown = this.dismissSecs
        },
        refreshPayment() {
            this.$router.go()
        },
        reOrder() {
            this.$router.push({ name: 'Create Order'})
        }
    },
    mounted(){
        this.getOrderDetail()
        this.getOrderItems()
    },
}

</script>
