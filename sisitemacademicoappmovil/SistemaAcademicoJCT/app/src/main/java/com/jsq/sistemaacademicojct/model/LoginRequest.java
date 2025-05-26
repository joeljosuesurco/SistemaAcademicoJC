package com.jsq.sistemaacademicojct.model;

public class LoginRequest {
    private String name_user;
    private String password;

    public LoginRequest(String name_user, String password) {
        this.name_user = name_user;
        this.password = password;
    }
}
