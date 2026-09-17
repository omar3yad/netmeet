const axios = require('axios');

const conversations = {};
const lastRequestTime = {};
const userQuota = {};

const getChatGPTResponse = async (socketId, message) => {
    const now = Date.now();

    //make sure there is a gap before user sends another message
    if (lastRequestTime[socketId] && now - lastRequestTime[socketId] < process.env.CHATGPT_SECONDS * 1000) {
        return 'Please wait before sending another message.';
    }

    if (!userQuota[socketId]) {
        userQuota[socketId] = 0;
    }

    //make sure to check the message limit
    if (userQuota[socketId] >= process.env.CHATGPT_MESSAGE_LIMIT) {
        return 'You have reached your meeting limit for chat requests.';
    }

    userQuota[socketId]++;

    lastRequestTime[socketId] = now;

    if (!conversations[socketId]) {
        conversations[socketId] = [];
    }

    //keep the conversation length under control
    if (conversations[socketId].length >= process.env.CHATGPT_MAX_CONVERSATION_LENGTH * 2) {
        conversations[socketId].splice(0, 2);
    }

    //add user message to conversation history
    conversations[socketId].push({ role: 'user', content: message });

    try {
        const response = await axios.post(
            process.env.CHATGPT_API_URL,
            {
                model: process.env.CHATGPT_MODEL,
                messages: conversations[socketId],
            },
            {
                headers: {
                    'Authorization': `Bearer ${process.env.OPENAI_API_KEY}`,
                    'Content-Type': 'application/json',
                },
            }
        );

        //get the ChatGPT reply
        const reply = response.data.choices[0].message.content;

        //add ChatGPT reply to conversation history
        conversations[socketId].push({ role: 'assistant', content: reply });

        return reply;
    } catch (error) {
        return 'An error occurred please try again later!';
    }
};

//remove socketId from the conversations array
const removeSocketId = function (socketId) {
    delete conversations[socketId];
    delete lastRequestTime[socketId];
    delete userQuota[socketId];
}

module.exports = { getChatGPTResponse, removeSocketId };
