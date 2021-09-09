import * as firebase from 'firebase';

const firebaseConfig = {
    databaseURL: "https://bdashboard-3108.firebaseio.com",   
    apiKey: "AIzaSyDKp9ljCI1890jIyifCE8Olx7jjrAz4eag",
    authDomain: "bdashboard-3108.firebaseapp.com",
    projectId: "bdashboard-3108",
    storageBucket: "bdashboard-3108.appspot.com",
    messagingSenderId: "106173860137",
    appId: "1:106173860137:web:25230c95e7271362238f38",
    measurementId: "G-CB2DDS566E"
}

const firebaseApp = firebase.initializeApp(firebaseConfig);

export const db = firebaseApp.firestore();