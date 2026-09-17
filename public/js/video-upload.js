const express = require('express');
const multer = require('multer');
const path = require('path');
const fs = require('fs');

const app = express();
const PORT = 3001;

// إعداد مكان حفظ الفيديوهات
const uploadDir = path.join(__dirname, 'uploads');
if (!fs.existsSync(uploadDir)) {
    fs.mkdirSync(uploadDir);
}

const storage = multer.diskStorage({
    destination: function (req, file, cb) {
        cb(null, uploadDir);
    },
    filename: function (req, file, cb) {
        cb(null, Date.now() + '-' + file.originalname);
    }
});

const upload = multer({ storage: storage });

// endpoint لاستقبال الفيديو
app.post('/upload-video', upload.single('file'), (req, res) => {
    if (!req.file) {
        return res.status(400).send('لم يتم استقبال أي ملف');
    }
    res.status(200).send('تم حفظ الفيديو بنجاح');
});

app.listen(PORT, () => {
    console.log(`Video upload server running on port ${PORT}`);
});
