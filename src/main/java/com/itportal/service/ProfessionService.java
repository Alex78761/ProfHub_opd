package com.itportal.service;

import com.itportal.model.Profession;
import com.itportal.repository.ProfessionRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

import java.util.List;

@Service
public class ProfessionService {

    private final ProfessionRepository professionRepository;

    @Autowired
    public ProfessionService(ProfessionRepository professionRepository) {
        this.professionRepository = professionRepository;
    }

    public Profession saveProfession(Profession profession) {
        return professionRepository.save(profession);
    }

    public List<Profession> getAllProfessions() {
        return professionRepository.findAll();
    }

    public Profession getProfessionById(Long id) {
        return professionRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Профессия не найдена"));
    }

    public List<Profession> getLatestProfessions(int count) {
        return professionRepository.findAll().stream()
                .sorted((p1, p2) -> {
                    if (p1.getCreatedAt() == null) return 1;
                    if (p2.getCreatedAt() == null) return -1;
                    return p2.getCreatedAt().compareTo(p1.getCreatedAt());
                })
                .limit(count)
                .toList();
    }
} 