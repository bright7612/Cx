var app = new Vue({
    el: '#app',
    data: {
        imgList: [
            {
                id: 1,
                width: 67,
                top: 435,
                left: 11,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 2,
                width: 40,
                top: 362,
                left: 27,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 3,
                width: 20,
                top: 342,
                left: 52,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 4,
                width: 40,
                top: 376,
                left: 73,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 5,
                width: 35,
                top: 416,
                left: 82,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 6,
                width: 74,
                top: 408,
                left: 120,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 7,
                width: 28,
                top: 473,
                left: 190,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 8,
                width: 55,
                top: 417,
                left: 200,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 9,
                width: 43,
                top: 466,
                left: 239,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 10,
                width: 35,
                top: 415,
                left: 263,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 11,
                width: 55,
                top: 447,
                left: 287,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 12,
                width: 31,
                top: 405,
                left: 302,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 13,
                width: 22,
                top: 390,
                left: 338,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 14,
                width: 60,
                top: 416,
                left: 341,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 15,
                width: 69,
                top: 346,
                left: 361,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 16,
                width: 18,
                top: 418,
                left: 402,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 17,
                width: 25,
                top: 318,
                left: 405,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 18,
                width: 51,
                top: 333,
                left: 439,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 19,
                width: 76,
                top: 253,
                left: 425,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 20,
                width: 56,
                top: 188,
                left: 426,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 21,
                width: 36,
                top: 219,
                left: 481,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 22,
                width: 21,
                top: 185,
                left: 486,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 23,
                width: 28,
                top: 158,
                left: 416,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 24,
                width: 51,
                top: 128,
                left: 443,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 25,
                width: 40,
                top: 112,
                left: 402,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 26,
                width: 30,
                top: 87,
                left: 428,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 27,
                width: 53,
                top: 51,
                left: 376,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 28,
                width: 40,
                top: 23,
                left: 337,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 29,
                width: 27,
                top: 12,
                left: 312,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 30,
                width: 18,
                top: 7,
                left: 295,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 31,
                width: 38,
                top: 185,
                left: 58,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 32,
                width: 60,
                top: 205,
                left: 94,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 33,
                width: 45,
                top: 153,
                left: 88,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 34,
                width: 36,
                top: 121,
                left: 122,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 35,
                width: 48,
                top: 153,
                left: 160,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 36,
                width: 56,
                top: 75,
                left: 161,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 37,
                width: 40,
                top: 114,
                left: 221,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 38,
                width: 40,
                top: 67,
                left: 234,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 39,
                width: 20,
                top: 93,
                left: 274,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 40,
                width: 60,
                top: 208,
                left: 188,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 41,
                width: 27,
                top: 176,
                left: 224,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 42,
                width: 66,
                top: 225,
                left: 247,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 43,
                width: 48,
                top: 296,
                left: 273,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 44,
                width: 30,
                top: 270,
                left: 316,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 45,
                width: 39,
                top: 340,
                left: 309,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 46,
                width: 48,
                top: 300,
                left: 340,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 47,
                width: 24,
                top: 450,
                left: 407,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 48,
                width: 44,
                top: 394,
                left: 432,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 49,
                width: 24,
                top: 479,
                left: 433,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 50,
                width: 34,
                top: 447,
                left: 448,
                img: './imgs/EdSheeran.jpg',
            },
            {
                id: 51,
                width: 29,
                top: 424,
                left: 477,
                img: './imgs/EdSheeran.jpg',
            }
        ],
        imgData: []
    },
    methods: {
        getImageData: function (session) {
            var _this = this;
            axios.get('http://cxdj.cmlzjz.com/dyuuu/smile/headimg',{
                params:{
                    id: session
                }
            })
            .then(function(res){
                var data = res.data;
                if (data.status === 1) {
                    _this.imgData = data.data;
                }
            })
            .catch(function(error){
                console.log(error)
            });
        }
    },
    mounted: function () {
        var session = this.$refs.session.innerText;
        this.getImageData(session);
    }
});