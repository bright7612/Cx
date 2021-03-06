var app = new Vue({
    el: '#app',
    data: {
        imgList: [
            {
                id: 1,
                width: 94,
                top: 628,
                left: 18,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 2,
                width: 56,
                top: 522,
                left: 41,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 3,
                width: 27,
                top: 493,
                left: 77,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 4,
                width: 56,
                top: 543,
                left: 106,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 5,
                width: 50,
                top: 600,
                left: 119,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 6,
                width: 105,
                top: 589,
                left: 175,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 7,
                width: 39,
                top: 682,
                left: 275,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 8,
                width: 79,
                top: 601,
                left: 289,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 9,
                width: 60,
                top: 672,
                left: 346,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 10,
                width: 50,
                top: 598,
                left: 380,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 11,
                width: 77,
                top: 644,
                left: 415,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 12,
                width: 43,
                top: 584,
                left: 437,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 13,
                width: 30,
                top: 562,
                left: 489,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 14,
                width: 86,
                top: 600,
                left: 492,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 15,
                width: 98,
                top: 499,
                left: 521,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 16,
                width: 24,
                top: 603,
                left: 580,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 17,
                width: 35,
                top: 459,
                left: 585,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 18,
                width: 72,
                top: 481,
                left: 634,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 19,
                width: 108,
                top: 366,
                left: 614,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 20,
                width: 79,
                top: 272,
                left: 615,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 21,
                width: 51,
                top: 317,
                left: 694,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 22,
                width: 29,
                top: 267,
                left: 701,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 23,
                width: 40,
                top: 229,
                left: 600,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 24,
                width: 72,
                top: 185,
                left: 639,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 25,
                width: 55,
                top: 163,
                left: 580,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 26,
                width: 42,
                top: 126,
                left: 618,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 27,
                width: 75,
                top: 74,
                left: 541,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 28,
                width: 58,
                top: 35,
                left: 487,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 29,
                width: 37,
                top: 19,
                left: 451,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 30,
                width: 25,
                top: 12,
                left: 426,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 31,
                width: 53,
                top: 268,
                left: 86,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 32,
                width: 85,
                top: 297,
                left: 138,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 33,
                width: 62,
                top: 221,
                left: 130,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 34,
                width: 51,
                top: 175,
                left: 178,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 35,
                width: 67,
                top: 222,
                left: 233,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 36,
                width: 80,
                top: 109,
                left: 235,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 37,
                width: 56,
                top: 165,
                left: 321,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 38,
                width: 56,
                top: 98,
                left: 340,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 39,
                width: 28,
                top: 135,
                left: 397,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 40,
                width: 86,
                top: 300,
                left: 274,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 41,
                width: 38,
                top: 254,
                left: 325,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 42,
                width: 94,
                top: 325,
                left: 358,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 43,
                width: 68,
                top: 428,
                left: 396,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 44,
                width: 42,
                top: 390,
                left: 457,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 45,
                width: 55,
                top: 490,
                left: 447,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 46,
                width: 67,
                top: 435,
                left: 492,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 47,
                width: 33,
                top: 651,
                left: 588,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 48,
                width: 61,
                top: 568,
                left: 625,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 49,
                width: 33,
                top: 691,
                left: 626,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 50,
                width: 47,
                top: 644,
                left: 648,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 51,
                width: 40,
                top: 612,
                left: 690,
                img: './imgs/EdSheeran.jpg',
            }
        ],
        imgData: [],
        user: {
            headimgurl: '',
            id: '',
            nickname: ''
        }
    },
    methods: {
        getLastData: function () {
            var _this = this;
            axios.get('http://cxdj.cmlzjz.com/Dyuuu/Smile/pc_headimg')
            .then(function(res){
                var data = res.data;
                // console.log(data);
                if (data.status === 1) {
                    _this.imgData = data.data.list;
                    _this.user = data.data.user;
                }
            })
            .catch(function(error){
                console.log(error)
            });
        },
        checkNew: function () {
            var _this = this;
            axios.get('http://cxdj.cmlzjz.com/Dyuuu/Smile/check_new', {
                params: {
                    id: _this.user.id
                }
            })
            .then(function(res){
                var data = res.data;
                // console.log(data);
                if (data.status === 1) {
                    if (data.data) {
                        _this.getLastData();
                    }
                }
            })
            .catch(function(error){
                console.log(error)
            });
        }
    },
    created: function () {
        this.checkNew();
    },
    mounted: function () {
        var _this = this;
        setInterval(function () {
            _this.checkNew();
        }, 3000);
    }
});