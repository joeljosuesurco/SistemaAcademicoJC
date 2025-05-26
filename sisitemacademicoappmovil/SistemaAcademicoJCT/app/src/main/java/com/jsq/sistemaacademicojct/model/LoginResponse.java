package com.jsq.sistemaacademicojct.model;

public class LoginResponse {
    private boolean success;
    private String message;
    private String token;
    private UserData data;

    public boolean isSuccess() {
        return success;
    }

    public String getMessage() {
        return message;
    }

    public String getToken() {
        return token;
    }

    public UserData getData() {
        return data;
    }
}